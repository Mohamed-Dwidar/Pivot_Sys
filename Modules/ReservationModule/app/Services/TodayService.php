<?php

namespace Modules\ReservationModule\app\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\AccountModule\app\Models\Account;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\SpaceModule\app\Models\Space;
use Modules\SpaceModule\app\Models\SubscriptionType;
use Modules\UnitModule\app\Models\Unit;

/**
 * The Today page (account + employees, same content): built ONLY from the account's own data — one section per subscription
 * type with Show on Home (home_order, then name), its layout by booking_mode; nothing is named in code (multi account).
 *
 * The business day (Settings › General, Account::businessDay()): D = [D cutoff:00, D+1 cutoff:00), cutoff = timeline "to" − 24
 * (Pivot: to 26 → 02:00). The timeline shows D from:00 → D to:00 (to > 24 = the next morning).
 * - hourly: the reservations STARTING in the business day are D's (counted, Due); the ones started before it (the previous
 *   evening) show for information only ("from yesterday", not counted again); timeline rows = the type's active units
 *   (grouped by space when the type has more than one space); an item ended before "from" = the Earlier column, one starting
 *   after "to" = the Later column; the rest in the grid (15 minute slots, clipped at the edges), in lanes when they overlap
 * - full_day: by their DATE (start_at day = D: the form gives a full day reservation the business date at night)
 * - period (no running counts per space, user 2026-10-15): renewals by the RENEWAL DATE (the day after the last day, user 2026-10-15):
 *   Renews today (= D) | Not renewed (before D, no counted renewal, not left) | Next N days (D+1 … D+N); a renewed one shows
 *   its renewal's end
 * Only counted reservations (not cancelled like ones).
 */
class TodayService
{
    // the timeline slot (minutes) — custom.css .tl-from-N / .tl-span-N (N = 1 … 120: 30 hours)
    const SLOT_MINUTES = 15;
    // the most lanes of one unit row (custom.css .tl-lane-N / .tl-lanes-N); more overlaps share the last lane
    const MAX_LANES = 12;

    public function page(int $accountId, Account $account, Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $today = $account->businessDate();
        [$from, $to] = $account->todayHours();
        [$dayStart, $dayEnd] = $account->businessDay($date);

        $context = [
            'accountId' => $accountId,
            'date' => $date,
            'dayStart' => $dayStart,
            'dayEnd' => $dayEnd,
            // the timeline window
            'windowStart' => $date->copy()->addHours($from),
            'windowEnd' => $date->copy()->addHours($to),
            'isToday' => $date->eq($today),
            'renewalDays' => $account->renewalDays(),
        ];

        $types = SubscriptionType::where('account_id', $accountId)->where('show_home', true)
            ->orderBy('home_order')->orderBy('name')->with('spaces')->get();
        $sections = $types->map(fn ($type) => match ($type->booking_mode) {
            'full_day' => $this->fullDaySection($type, $context),
            'period' => $this->periodSection($type, $context),
            default => $this->hourlySection($type, $context),
        });

        return [
            'date' => $date,
            'today' => $today,
            'isToday' => $context['isToday'],
            // at night (before the cutoff) the real date differs from the business date: the header hint
            'nightNow' => $context['isToday'] && !$today->isSameDay(now()) ? now() : null,
            'hours' => [$from, $to],
            'windowStart' => $context['windowStart'],
            'sections' => $sections,
            // the summary row: hourly / full day counts, renewals that need action, the remaining of the day
            'renewals' => $sections->where('mode', 'period')->sum('needAction'),
            'hasPeriod' => $sections->contains('mode', 'period'),
            'due' => round($sections->whereIn('mode', ['hourly', 'full_day'])->sum('due'), 2),
            // + New: every type of the account (pick a type first), with its start for the selected day
            'newTypes' => SubscriptionType::where('account_id', $accountId)->orderBy('home_order')->orderBy('name')->get()
                ->map(fn ($type) => ['type' => $type, 'start' => $this->newStart($type, $context, $from)]),
        ];
    }

    // the start of a new reservation of the type for the day (+ New, + <type>): hourly on today = now, on another day = the
    // timeline start; full day / period = the date
    public function newStart(SubscriptionType $type, array $context, int $from): string
    {
        if ($type->isHourly()) {
            return $context['isToday'] ? now()->format('Y-m-d H:i') : $context['date']->copy()->addHours($from)->format('Y-m-d H:i');
        }
        return $context['date']->format('Y-m-d');
    }

    // the type's counted reservations for the page (member, unit, package for the badges)
    private function reservations(SubscriptionType $type, int $accountId)
    {
        return Reservation::where('account_id', $accountId)->where('subscription_type_id', $type->id)->counted()
            ->with('member', 'space', 'unit.color', 'package', 'status');
    }

    // remaining owed (> 0) of reservations paid on their own (not inside a package)
    private function dueOf(Collection $reservations): float
    {
        return round($reservations->filter(fn ($reservation) => $reservation->canTakePayments() && $reservation->remaining > 0)->sum('remaining'), 2);
    }

    private function hourlySection(SubscriptionType $type, array $context): array
    {
        [$dayStart, $dayEnd, $windowStart, $windowEnd] = [$context['dayStart'], $context['dayEnd'], $context['windowStart'], $context['windowEnd']];
        $midnight = $context['date'];
        // starting in the business day, or started before it and still running after midnight of D (the previous evening)
        $reservations = $this->reservations($type, $context['accountId'])
            ->where('start_at', '<', $dayEnd->max($windowEnd))
            ->where(fn ($query) => $query->where('start_at', '>=', $dayStart)
                ->orWhere(fn ($query) => $query->where('end_at', '>', $midnight)->orWhereNull('end_at')))
            ->orderBy('start_at')->orderBy('id')->get()
            // an end before the start (old same-day rows) = it ends at its start
            ->each(function ($reservation) use ($dayStart) {
                $reservation->today_own = $reservation->start_at->gte($dayStart);
            });
        $own = $reservations->where('today_own', true);

        $slots = intdiv((int) $windowStart->diffInMinutes($windowEnd), self::SLOT_MINUTES);
        $units = Unit::where('account_id', $type->account_id)->where('subscription_type_id', $type->id)->where('is_active', true)
            ->whereIn('space_id', $type->spaces->pluck('id'))->with('color')->ordered()->get();
        // a reservation of a unit not shown (inactive / moved): its own row, so nothing is hidden
        $missing = $reservations->pluck('unit_id')->unique()->diff($units->pluck('id'));
        if ($missing->isNotEmpty()) {
            $units = $units->concat(Unit::withTrashed()->whereIn('id', $missing)->with('color')->get());
        }

        $rows = $units->map(fn ($unit) => $this->timelineRow($unit, $reservations->where('unit_id', $unit->id), $windowStart, $windowEnd, $slots));
        $spaceIds = $units->pluck('space_id')->unique();
        $spaces = Space::withTrashed()->whereIn('id', $spaceIds)->ordered()->get();
        $groups = $spaces->map(fn ($space) => ['space' => $space, 'rows' => $rows->filter(fn ($row) => (int) $row['unit']->space_id === (int) $space->id)->values()])
            ->filter(fn ($group) => $group['rows']->isNotEmpty())->values();

        return [
            'mode' => 'hourly',
            'type' => $type,
            'anchor' => 'today-type-' . $type->id,
            'count' => $own->count(),
            'due' => $this->dueOf($own),
            'slots' => $slots,
            'hours' => range(0, intdiv($slots * self::SLOT_MINUTES, 60) - 1),
            'groups' => $groups,
            'multiSpace' => $groups->count() > 1,
            'hasEarlier' => $rows->contains(fn ($row) => $row['earlier']->isNotEmpty()),
            'hasLater' => $rows->contains(fn ($row) => $row['later']->isNotEmpty()),
            // the List view: the ones from yesterday first, then by start
            'list' => $reservations->sortBy(fn ($reservation) => [$reservation->today_own ? 1 : 0, $reservation->start_at->getTimestamp(), $reservation->id])->values(),
        ];
    }

    /**
     * One unit row of the timeline: 'earlier' (ended before the window), 'later' (starting after it), 'blocks' in the grid
     * ['reservation', 'from' (slot 1…), 'span', 'lane' (1…), 'clipStart', 'clipEnd'], 'lanes'
     */
    private function timelineRow(Unit $unit, Collection $reservations, Carbon $windowStart, Carbon $windowEnd, int $slots): array
    {
        $earlier = collect();
        $later = collect();
        $blocks = [];
        $laneEnds = [];
        foreach ($reservations as $reservation) {
            $start = $reservation->start_at;
            $end = $reservation->end_at ? $reservation->realEnd() : $windowEnd;
            if ($end->lte($windowStart)) {
                $earlier->push($reservation);
                continue;
            }
            if ($start->gte($windowEnd)) {
                $later->push($reservation);
                continue;
            }
            $from = intdiv((int) max(0, $windowStart->diffInMinutes($start, false)), self::SLOT_MINUTES) + 1;
            $until = (int) ceil(min($windowStart->diffInMinutes($windowEnd), $windowStart->diffInMinutes($end, false)) / self::SLOT_MINUTES);
            $span = max(1, min($slots - $from + 1, $until - $from + 1));
            // the first lane free at its start (else the last lane)
            $lane = 1;
            while (isset($laneEnds[$lane]) && $laneEnds[$lane] > $from && $lane < self::MAX_LANES) {
                $lane++;
            }
            $laneEnds[$lane] = max($laneEnds[$lane] ?? 0, $from + $span);
            $blocks[] = ['reservation' => $reservation, 'from' => $from, 'span' => $span, 'lane' => $lane,
                'clipStart' => $start->lt($windowStart), 'clipEnd' => $end->gt($windowEnd)];
        }

        return ['unit' => $unit, 'earlier' => $earlier, 'later' => $later, 'blocks' => $blocks, 'lanes' => max(1, count($laneEnds))];
    }

    private function fullDaySection(SubscriptionType $type, array $context): array
    {
        $date = $context['date'];
        $rows = $this->reservations($type, $context['accountId'])
            ->where('start_at', '>=', $date)->where('start_at', '<', $date->copy()->addDay())
            ->orderBy('created_at')->orderBy('id')->get()
            // "In": the check-in time — imported rows keep the old start time, the app saves the date only (00:00) → when added
            ->each(function ($reservation) {
                $reservation->today_in = $reservation->start_at->format('H:i:s') === '00:00:00' ? $reservation->created_at : $reservation->start_at;
            })
            ->sortBy(fn ($reservation) => [$reservation->today_in?->getTimestamp() ?? 0, $reservation->id])->values();

        return [
            'mode' => 'full_day',
            'type' => $type,
            'anchor' => 'today-type-' . $type->id,
            'count' => $rows->count(),
            'due' => $this->dueOf($rows),
            'rows' => $rows,
        ];
    }

    private function periodSection(SubscriptionType $type, array $context): array
    {
        $date = $context['date'];
        $base = fn () => $this->reservations($type, $context['accountId']);

        $periods = fn () => $base()->renewalPeriods()->with('renewedBy');
        $renewalDate = Reservation::RENEWAL_DATE_SQL;
        $renewsToday = $periods()->whereRaw("$renewalDate = ?", [$date->toDateString()])->orderBy('member_id')->get();
        // the fewest days since the renewal date first (user 2026-10-15)
        $notRenewed = $periods()->whereRaw("$renewalDate < ?", [$date->toDateString()])->notRenewed()
            ->orderByRaw("$renewalDate DESC")->orderByDesc('id')->get();
        $next = $periods()->whereRaw("$renewalDate BETWEEN ? AND ?", [$date->copy()->addDay()->toDateString(), $date->copy()->addDays($context['renewalDays'])->toDateString()])
            ->orderByRaw($renewalDate)->orderBy('id')->get();

        return [
            'mode' => 'period',
            'type' => $type,
            'anchor' => 'today-renewals-' . $type->id,
            'renewsToday' => $renewsToday,
            'notRenewed' => $notRenewed,
            'next' => $next,
            'renewalDays' => $context['renewalDays'],
            // the summary "Renewals": renews today, not renewed yet + not renewed
            'needAction' => $renewsToday->whereNull('renewedBy')->count() + $notRenewed->count(),
        ];
    }
}
