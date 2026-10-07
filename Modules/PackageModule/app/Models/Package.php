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

/**
 * A package of a member: amount - discount_percentage = after_discount (the package price),
 * total_amount = net amount of its reservations, remaining = after_discount - total_amount (see recalculate()).
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
    public function recalculate(): void
    {
        $this->total_amount = round((float) $this->reservations()->sum('net_amount'), 2);
        $this->remaining = round($this->after_discount - $this->total_amount, 2);
        $this->save();
    }

    // "2026-10-01 → 2026-12-31"
    public function getPeriodAttribute(): string
    {
        if (!$this->date_from && !$this->date_to) {
            return '-';
        }
        return ($this->date_from?->format('Y-m-d') ?? '...') . ' → ' . ($this->date_to?->format('Y-m-d') ?? '...');
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
