<?php

namespace Modules\ReservationModule\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Models\Member;
use Modules\PackageModule\app\Models\Package;
use Modules\PlanModule\app\Models\Plan;
use Modules\SpaceModule\app\Models\Space;
use Modules\SpaceModule\app\Models\SubscriptionType;
use Modules\UnitModule\app\Models\Unit;

/**
 * A reservation of a unit (of a space) with a plan for a member, made by the account or one of its employees.
 * net_amount = amount - discount_value (discount_percentage is the same discount in %).
 * repeat_id: the reservation this one repeats (repeat()), repeats(): the reservations made from this one.
 */
class Reservation extends Model
{
    use SoftDeletes;

    // the most copies one "repeat until" can create
    const MAX_REPEATS = 365;

    const REPEAT_FREQUENCIES = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];

    protected $fillable = [
        'account_id', 'member_id', 'space_id', 'unit_id', 'plan_id', 'package_id', 'subscription_type_id', 'reservation_status_id',
        'start_at', 'end_at', 'subscription_day', 'is_continue', 'all_day', 'repeat_id', 'is_repeat', 'repeat_frequency', 'repeat_interval',
        'number_of_peoples', 'amount', 'discount_percentage', 'discount_value', 'net_amount', 'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_continue' => 'boolean',
        'all_day' => 'boolean',
        'is_repeat' => 'boolean',
        'amount' => 'float',
        'discount_percentage' => 'float',
        'discount_value' => 'float',
        'net_amount' => 'float',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // still shown when the related record was deleted later
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withTrashed();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class)->withTrashed();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function subscriptionType(): BelongsTo
    {
        return $this->belongsTo(SubscriptionType::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ReservationStatus::class, 'reservation_status_id')->withTrashed();
    }

    // the reservation this one repeats (self relation)
    public function repeat(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repeat_id');
    }

    // the reservations made by repeating this one
    public function repeats(): HasMany
    {
        return $this->hasMany(self::class, 'repeat_id');
    }

    // who added the reservation (users: the account or an employee)
    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRepeatFrequencyLabelAttribute(): ?string
    {
        return self::REPEAT_FREQUENCIES[$this->repeat_frequency] ?? null;
    }

    // the display formats of the start / end (a time is shown only for a time based plan)
    const DATE_FORMAT = 'd-m-Y';
    const TIME_FORMAT = 'h:i A';

    // "21-10-2026" / "21-10-2026 | 11:00 AM" (time based plan)
    public function formatAt($at): string
    {
        if (!$at) {
            return '-';
        }
        return $at->format(self::DATE_FORMAT) . ($this->plan?->is_time_based ? ' | ' . $at->format(self::TIME_FORMAT) : '');
    }

    // the same day:  "21-10-2026 | 11:00 AM → 01:30 PM" (time based) / "21-10-2026"
    // other days:    "21-10-2026 | 11:00 AM → 22-10-2026 | 01:30 PM" / "21-10-2026 → 25-10-2026"
    // continuous:    "From 21-10-2026 | 11:00 AM (continues)"
    public function getPeriodAttribute(): string
    {
        if (!$this->start_at) {
            return '-';
        }
        if ($this->is_continue) {
            return 'From ' . $this->formatAt($this->start_at) . ' (continues)';
        }
        if (!$this->end_at) {
            return $this->formatAt($this->start_at) . ' → -';
        }
        if ($this->start_at->isSameDay($this->end_at)) {
            return $this->plan?->is_time_based
                ? $this->formatAt($this->start_at) . ' → ' . $this->end_at->format(self::TIME_FORMAT)
                : $this->start_at->format(self::DATE_FORMAT);
        }
        return $this->formatAt($this->start_at) . ' → ' . $this->formatAt($this->end_at);
    }

    // the starts of the repeated copies: every $interval $frequency after $start, until the date $until (included);
    // from the first start each time (Jan 31 monthly => Feb 28, Mar 31 ...), at most $limit
    public static function repeatStarts(Carbon $start, string $frequency, int $interval, Carbon $until, int $limit = self::MAX_REPEATS + 1): array
    {
        $starts = [];
        for ($k = 1; count($starts) < $limit; $k++) {
            $step = $k * max(1, $interval);
            $next = match ($frequency) {
                'daily' => $start->copy()->addDays($step),
                'weekly' => $start->copy()->addWeeks($step),
                'yearly' => $start->copy()->addYearsNoOverflow($step),
                default => $start->copy()->addMonthsNoOverflow($step),
            };
            if ($next->toDateString() > $until->toDateString()) {
                break;
            }
            $starts[] = $next;
        }
        return $starts;
    }

    // [discount_value, discount_percentage, net_amount] from the amount and the one the user typed ($type: percentage | value | net)
    public static function calculate(float $amount, ?float $percentage, ?float $value, string $type = 'percentage', ?float $net = null): array
    {
        $amount = max(0, $amount);
        if ($type === 'net') {
            $value = $amount - min(max(0, (float) $net), $amount);
            $percentage = $amount > 0 ? $value / $amount * 100 : 0;
        } elseif ($type === 'value') {
            $value = min(max(0, (float) $value), $amount);
            $percentage = $amount > 0 ? $value / $amount * 100 : 0;
        } else {
            $percentage = min(max(0, (float) $percentage), 100);
            $value = $amount * $percentage / 100;
        }

        return [round($value, 2), round($percentage, 2), round($amount - $value, 2)];
    }

    public function scopeFilter($query, array $filters = [])
    {
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->whereHas('member', fn ($query) => $query->where('name', 'like', $search)->orWhere('phone', 'like', str_replace(' ', '', $search)));
        }

        foreach (['reservation_status_id', 'space_id', 'package_id', 'member_id', 'subscription_type_id'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('start_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('start_at', '<=', $filters['date_to']);
        }

        return $query;
    }
}
