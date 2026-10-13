<?php

namespace Modules\ReservationModule\app\Models;

use App\Helpers\SafeMorphHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Models\Member;
use Modules\PackageModule\app\Models\Package;
use Modules\PaymentModule\app\Contracts\Payable;
use Modules\PaymentModule\app\Models\Concerns\HasPayments;
use Modules\PlanModule\app\Models\Plan;
use Modules\SpaceModule\app\Models\Space;
use Modules\SpaceModule\app\Models\SubscriptionType;
use Modules\UnitModule\app\Models\Unit;

/**
 * A reservation of a unit (of a space) with a plan for a member, made by the account or one of its employees.
 * net_amount = amount - discount_value (discount_percentage is the same discount in %).
 * repeat_id: the reservation this one repeats (repeat()), repeats(): the reservations made from this one.
 */
class Reservation extends Model implements Payable
{
    use HasPayments, SafeMorphHelper, SoftDeletes;

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
        'number_of_peoples', 'amount', 'discount_percentage', 'discount_value', 'net_amount', 'paid_amount', 'remaining', 'notes',
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
        'paid_amount' => 'float',
        'remaining' => 'float',
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

    // Payable: its net amount, 0 with a not counted status (cancelled ...); inside a package it is paid through the package
    public function payableAmount(): float
    {
        return $this->status && !$this->status->is_counted ? 0 : (float) $this->net_amount;
    }

    public function payableMemberId(): int
    {
        return (int) $this->member_id;
    }

    public function canTakePayments(): bool
    {
        return (int) $this->package_id === 0;
    }

    // HasPayments::scopePaymentStatus(): paid on its own / through its package
    public function scopePayableAlone($query)
    {
        return $query->where('reservations.package_id', 0);
    }

    public function scopeNotPayableAlone($query)
    {
        return $query->where('reservations.package_id', '>', 0);
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

    // the counted reservations of the unit that overlap start -> end (no end = it continues): they start before the end
    // and end after the start (or continue); the deleted ones and the not counted statuses (cancelled ...) are not counted
    public function scopeOverlapping($query, $unitId, $start, $end = null)
    {
        $query->where('unit_id', $unitId)
            ->whereNotIn('reservation_status_id', ReservationStatus::notCountedIds())
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>', $start));
        if ($end) {
            $query->where('start_at', '<', $end);
        }
        return $query;
    }

    // the list tabs (docs/reservations-list-plan.md, simplified 2026-10-13): today (default, running today) | upcoming | all
    const TABS = [
        'today' => 'Today',
        'upcoming' => 'Upcoming',
        'all' => 'All',
    ];

    /**
     * Running at some moment of [from, to): starts before "to" and ends after "from".
     * An end before the start (old same-day rows) = it ends at its start: it counts on its start day.
     * Plain ranges (no whereDate), so the (account_id, start_at) / (account_id, end_at) indexes can be used.
     */
    public function scopeRunningBetween($query, Carbon $from, Carbon $to)
    {
        return $query->where('start_at', '<', $to)
            ->where(fn ($query) => $query->where('end_at', '>', $from)->orWhere('start_at', '>=', $from)->orWhereNull('end_at'));
    }

    /**
     * The rows of a list tab (an unknown tab = all). Today / Upcoming leave the contract periods out (one row per month)
     * unless the type filter ($filters['subscription_type_id']) is chosen: then that type's rows show on any tab.
     */
    public function scopeTab($query, ?string $tab, array $filters = [])
    {
        if (in_array($tab, ['today', 'upcoming'], true) && empty($filters['subscription_type_id'])) {
            $query->notContracts();
        }

        return match ($tab) {
            'today' => $query->runningBetween(today(), today()->addDay()),
            'upcoming' => $query->where('start_at', '>=', today()->addDay()),
            default => $query,
        };
    }

    // the default order of a tab (until a column is clicked): today = starting today first (by start time), then the
    // ones already running (long ones); upcoming = nearest first; all = newest first
    public function scopeTabOrder($query, ?string $tab)
    {
        if ($tab === 'today') {
            $query->orderByRaw('start_at >= ? DESC', [today()]);
        }

        return in_array($tab, ['today', 'upcoming'], true)
            ? $query->orderBy('start_at')->orderBy('id')
            : $query->orderByDesc('start_at')->orderByDesc('id');
    }

    // the counted reservations first, the not counted ones (cancelled, pending ...) after: always the first order key
    public function scopeCountedFirst($query)
    {
        $ids = ReservationStatus::notCountedIds();
        return $ids ? $query->orderByRaw('reservation_status_id IN (' . implode(',', array_map('intval', $ids)) . ')') : $query;
    }

    public function scopeCounted($query)
    {
        return $query->whereNotIn('reservation_status_id', ReservationStatus::notCountedIds());
    }

    // counted (its status counts: not cancelled / pending ...); no status = counted
    public function isCounted(): bool
    {
        return $this->status?->is_counted ?? true;
    }

    // the end used for the length / the badges: an end before the start (old same-day rows) = the start
    public function realEnd(): ?Carbon
    {
        return $this->end_at && $this->start_at && $this->end_at->lt($this->start_at) ? $this->start_at : $this->end_at;
    }

    /**
     * The badge of a reservation longer than a day (the list Period column): "running · day 12 of 90" while it runs,
     * "ends 30-11" before, "ended 30-11" after (the year only when not this year). null for a short one.
     */
    public function getLongBadgeAttribute(): ?string
    {
        $end = $this->realEnd();
        if (!$this->start_at || !$end || $this->start_at->diffInHours($end) <= 24) {
            return null;
        }

        $date = $end->format($end->year === now()->year ? 'd-m' : 'd-m-Y');
        if (now()->lt($this->start_at)) {
            return 'ends ' . $date;
        }
        if (now()->gte($end)) {
            return 'ended ' . $date;
        }
        $days = (int) ceil($this->start_at->diffInMinutes($end) / 1440);
        $day = min($days, (int) floor($this->start_at->diffInMinutes(now()) / 1440) + 1);

        return 'running · day ' . $day . ' of ' . $days;
    }

    // the Subscriptions tab's filter (docs/reservations-list-plan.md)
    const SUBSCRIPTION_TABS = [
        'active' => 'Active Now',
        'ending7' => 'Ending within 7 Days',
        'ending30' => 'Ending within 30 Days',
        'ended' => 'Ended, Not Renewed',
    ];

    // "ended, not renewed": ended within this many days by default (a date range looks further back)
    const ENDED_DAYS = 60;

    // the ids of the contract subscription types (subscription_types.is_contract: monthly subscriptions, one row per period)
    public static function contractTypeIds(): array
    {
        return once(fn () => SubscriptionType::where('is_contract', true)->pluck('id')->map(fn ($id) => (int) $id)->all());
    }

    // contract periods (one reservation per renewal period)
    public function scopeContracts($query)
    {
        return $query->whereIn('subscription_type_id', self::contractTypeIds() ?: [0]);
    }

    public function scopeNotContracts($query)
    {
        return ($ids = self::contractTypeIds()) ? $query->whereNotIn('subscription_type_id', $ids) : $query;
    }

    public function isContract(): bool
    {
        return in_array((int) $this->subscription_type_id, self::contractTypeIds(), true);
    }

    /**
     * SQL: the next period exists = the member has a later counted contract reservation in the same space (renewed),
     * for "reservations" in the query. In the data the renewal is the same member + space in 1,092 / 1,113 cases (2026-10-12).
     */
    public static function renewedSql(): string
    {
        $types = self::contractTypeIds() ?: [0];
        $notCounted = array_map('intval', ReservationStatus::notCountedIds()) ?: [0];

        return 'EXISTS (SELECT 1 FROM reservations next WHERE next.account_id = reservations.account_id'
            . ' AND next.member_id = reservations.member_id AND next.space_id = reservations.space_id'
            . ' AND next.start_at > reservations.start_at AND next.deleted_at IS NULL'
            . ' AND next.subscription_type_id IN (' . implode(',', $types) . ')'
            . ' AND next.reservation_status_id NOT IN (' . implode(',', $notCounted) . '))';
    }

    // + the "renewed" column (0 / 1)
    public function scopeWithRenewed($query)
    {
        return $query->select('reservations.*')->selectRaw(self::renewedSql() . ' AS renewed');
    }

    public function scopeNotRenewed($query)
    {
        return $query->whereRaw('NOT ' . self::renewedSql());
    }

    /**
     * The Subscriptions tab's filter (counted contract periods with a member): active (running now, renewed or not) |
     * ending7 / ending30 (end within 7 / 30 days, not renewed) | ended (ended, not renewed: the member's latest one in that
     * space; ended within ENDED_DAYS days, or in the date range on the end date).
     */
    public function scopeSubscriptionTab($query, string $tab, array $filters = [])
    {
        $query->contracts()->counted()->where('member_id', '>', 0);
        $now = now();

        return match ($tab) {
            'ending7', 'ending30' => $query->where('end_at', '>', $now)->where('end_at', '<=', $now->copy()->addDays($tab === 'ending7' ? 7 : 30))->notRenewed(),
            'ended' => $query->where('end_at', '<=', $now)->notRenewed()
                ->where('end_at', '>=', !empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : $now->copy()->subDays(self::ENDED_DAYS)->startOfDay())
                ->when(!empty($filters['date_to']), fn ($query) => $query->where('end_at', '<', Carbon::parse($filters['date_to'])->addDay()->startOfDay())),
            default => $query->where('start_at', '<=', $now)->where(fn ($query) => $query->where('end_at', '>', $now)->orWhereNull('end_at')),
        };
    }

    // days left (running / future end) or days since it ended (negative)
    public function getDaysLeftAttribute(): ?int
    {
        $end = $this->realEnd();
        return $end ? (int) floor(now()->diffInMinutes($end, false) / 1440) : null;
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

        // ranges (not whereDate) so the start_at index can be used
        if (!empty($filters['date_from'])) {
            $query->where('start_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (!empty($filters['date_to'])) {
            $query->where('start_at', '<', Carbon::parse($filters['date_to'])->addDay()->startOfDay());
        }

        // unpaid / partial / paid / credit / nothing_due / elsewhere (in a package)
        if (!empty($filters['payment_status'])) {
            $query->paymentStatus($filters['payment_status']);
        }

        return $query;
    }
}
