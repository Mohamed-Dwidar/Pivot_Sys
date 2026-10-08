<?php

namespace Modules\PackageModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Models\Member;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\ReservationModule\app\Models\ReservationStatus;

/**
 * A package of a member: its price (amount) = the net amounts of its reservations,
 * - discount_percentage = after_discount (the package net), see recalculate() (after every reservation change).
 */
class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id', 'member_id', 'name', 'date_from', 'date_to', 'is_active',
        'discount_percentage', 'total_amount', 'amount', 'after_discount', 'remaining', 'notes',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'is_active' => 'boolean',
        'discount_percentage' => 'float',
        'total_amount' => 'float',
        'amount' => 'float',
        'after_discount' => 'float',
        'remaining' => 'float',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // who added the package (users)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    // [discount_percentage, after_discount] from the amount and the one typed ($type: percentage | after)
    public static function calculate(float $amount, ?float $percentage, ?float $afterDiscount, string $type = 'percentage'): array
    {
        $amount = max(0, $amount);
        if ($type === 'after') {
            $afterDiscount = min(max(0, (float) $afterDiscount), $amount);
            $percentage = $amount > 0 ? ($amount - $afterDiscount) / $amount * 100 : 0;
        } else {
            $percentage = min(max(0, (float) $percentage), 100);
            $afterDiscount = $amount - $amount * $percentage / 100;
        }

        return [round($percentage, 2), round($afterDiscount, 2)];
    }

    // total_amount / remaining from its reservations (after a reservation is saved / deleted)
    // the package price = the net amounts of its reservations, then its discount % => the package net (after_discount)
    // (total_amount = the same sum, remaining = the net: nothing is paid yet)
    public function recalculate(): void
    {
        $sum = $this->reservationsTotal();
        [, $afterDiscount] = self::calculate($sum, (float) $this->discount_percentage, null, 'percentage');

        // the dates: the first start / the last end of its counted reservations (no end when one of them continues)
        $counted = $this->reservations()->whereNotIn('reservation_status_id', ReservationStatus::notCountedIds());
        $this->date_from = (clone $counted)->min('start_at');
        $this->date_to = (clone $counted)->where(fn ($query) => $query->whereNull('end_at')->orWhere('is_continue', true))->exists()
            ? null
            : (clone $counted)->max('end_at');

        $this->amount = $sum;
        $this->total_amount = $sum;
        $this->after_discount = $afterDiscount;
        $this->remaining = $afterDiscount;
        $this->save();
    }

    // the total hours of its reservations (start -> end, a whole day = 24 hours);
    // the continuous ones have no end: not counted, their number is returned as 'open'
    public function reservationsHours(): array
    {
        $minutes = 0;
        $open = 0;
        foreach ($this->reservations as $reservation) {
            // a not counted status (cancelled ...) is not in the hours
            if ($reservation->status && !$reservation->status->is_counted) {
                continue;
            }
            if (!$reservation->start_at || !$reservation->end_at) {
                $open++;
                continue;
            }
            // a whole day ends at 23:59:59: count the last second as a full minute
            $minutes += (int) ceil($reservation->start_at->diffInSeconds($reservation->end_at, false) / 60);
        }

        return ['hours' => round(max(0, $minutes) / 60, 2), 'open' => $open];
    }

    // the price now: the net amounts of its counted reservations (not the cancelled like statuses), 0 for a new package
    public function reservationsTotal(): float
    {
        return $this->exists
            ? round((float) $this->reservations()->whereNotIn('reservation_status_id', ReservationStatus::notCountedIds())->sum('net_amount'), 2)
            : 0;
    }

    // from its reservations: "01-10-2026 → 31-12-2026" / "From 01-10-2026 (continues)" / "-" (no reservations)
    public function getPeriodAttribute(): string
    {
        if (!$this->date_from) {
            return '-';
        }
        $format = Reservation::DATE_FORMAT;
        return $this->date_to
            ? $this->date_from->format($format) . ' → ' . $this->date_to->format($format)
            : 'From ' . $this->date_from->format($format) . ' (continues)';
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', $search)
                    ->orWhereHas('member', fn ($query) => $query->where('name', 'like', $search)->orWhere('phone', 'like', str_replace(' ', '', $search)));
            });
        }

        if (!empty($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query;
    }
}
