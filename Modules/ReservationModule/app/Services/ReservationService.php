<?php

namespace Modules\ReservationModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\PackageModule\app\Models\Package;
use Modules\PackageModule\app\Services\PackageService;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\ReservationModule\app\Repositories\ReservationRepository;
use Modules\PlanModule\app\Models\Plan;
use Modules\SpaceModule\app\Models\SubscriptionType;
use Modules\UnitModule\app\Models\Unit;

/**
 * Every method takes the owner account id (the account, or the account of the logged in employee).
 * The amounts are calculated here again (the form calculates them while typing), and the package totals are kept up to date.
 */
class ReservationService
{
    private $reservationRepository;
    private $packageService;
    private $statusService;

    public function __construct(ReservationRepository $reservationRepository, PackageService $packageService, ReservationStatusService $statusService)
    {
        $this->reservationRepository = $reservationRepository;
        $this->packageService = $packageService;
        $this->statusService = $statusService;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->reservationRepository->forAccount($accountId)->filter($filters);
    }

    // 404 when the reservation belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->reservationRepository->forAccount($accountId)->with('creatable.userable', 'repeat', 'repeats', 'unit.color')->findOrFail($id);
    }

    // $creator: the logged in user (account or employee) who makes the reservation
    public function create($accountId, array $data, Model $creator)
    {
        return DB::transaction(function () use ($accountId, $data, $creator) {
            // the status is not chosen in the form: a new reservation gets the first active status
            $reservation = $this->reservationRepository->makeModel()->newInstance($this->reservationData($accountId, $data) + [
                'account_id' => $accountId,
                'reservation_status_id' => $this->statusService->defaultId($accountId),
            ]);
            $reservation->creatable()->associate($creator);
            $reservation->save();

            // repeat: the copies until the "repeat until" date
            if ($reservation->is_repeat && !empty($data['repeat_until'])) {
                $this->createRepeats($reservation, Carbon::parse($data['repeat_until']));
            }

            $this->packageService->recalculate($reservation->package_id);
            return $reservation;
        });
    }

    public function update($accountId, $id, array $data)
    {
        $reservation = $this->findOne($accountId, $id);

        return DB::transaction(function () use ($reservation, $accountId, $data) {
            $oldPackageId = $reservation->package_id;
            // the repeat is chosen on create only, it is kept as it is
            $reservation->update(Arr::except($this->reservationData($accountId, $data), ['is_repeat', 'repeat_frequency', 'repeat_interval']));

            // the old and the new package (it can be changed)
            $this->packageService->recalculate($oldPackageId);
            if ($reservation->package_id != $oldPackageId) {
                $this->packageService->recalculate($reservation->package_id);
            }
            return $reservation;
        });
    }

    // the status menu of the reservation view; the package is calculated again (a not counted status is not in its price)
    public function changeStatus($accountId, $id, $statusId)
    {
        $reservation = $this->findOne($accountId, $id);

        return DB::transaction(function () use ($reservation, $statusId) {
            $reservation->update(['reservation_status_id' => $statusId]);
            $this->packageService->recalculate($reservation->package_id);
            return $reservation->load('status');
        });
    }

    // soft delete
    public function deleteOne($accountId, $id)
    {
        $reservation = $this->findOne($accountId, $id);

        return DB::transaction(function () use ($reservation) {
            $deleted = $reservation->delete();
            $this->packageService->recalculate($reservation->package_id);
            return $deleted;
        });
    }

    // the copies of a repeated reservation: same data, moved to each repeat start (same duration), repeat_id = the original
    private function createRepeats(Reservation $reservation, Carbon $until): int
    {
        $starts = Reservation::repeatStarts($reservation->start_at, $reservation->repeat_frequency, (int) $reservation->repeat_interval, $until, Reservation::MAX_REPEATS);
        $duration = $reservation->end_at ? $reservation->start_at->diffInSeconds($reservation->end_at) : null;

        foreach ($starts as $start) {
            $copy = $reservation->replicate();
            $copy->start_at = $start;
            $copy->end_at = $duration !== null ? $start->copy()->addSeconds($duration) : null;
            $copy->subscription_day = $start->day;
            $copy->is_repeat = false;
            $copy->repeat_frequency = 'monthly';
            $copy->repeat_interval = 1;
            $copy->repeat_id = $reservation->id;
            $copy->save();
        }

        return count($starts);
    }

    // [start, end] of the form data: a time based plan = the date + the time, else the whole days; no end when it continues
    public function period(array $data): array
    {
        $timeBased = (bool) Plan::find($data['plan_id'] ?? null)?->is_time_based;
        $isContinue = !empty($data['is_continue']) && SubscriptionType::find($data['subscription_type_id'] ?? null)?->auto_renew;

        $startAt = $timeBased && !empty($data['start_time'])
            ? Carbon::parse($data['start_at'] . ' ' . $data['start_time'])
            : Carbon::parse($data['start_at'])->startOfDay();
        $endAt = $isContinue || empty($data['end_at']) ? null
            : ($timeBased && !empty($data['end_time']) ? Carbon::parse($data['end_at'] . ' ' . $data['end_time']) : Carbon::parse($data['end_at'])->endOfDay());

        return [$startAt, $endAt];
    }

    // (the reason is HTML: escaped texts + links to the reservations)
    // the unit must have a free place in the period (and in every repeat on create): the reason when it does not, null when free.
    // The unit concurrent usage = how many reservations can use it at the same time (1 = only one): the period is refused when,
    // at some moment of it, the unit already has that many reservations at the same time.
    // Not counted: the reservation itself ($ignoreId, edit), the deleted ones and the not counted statuses (cancelled ...)
    public function availabilityError(array $data, $ignoreId = null): ?string
    {
        $unit = Unit::find($data['unit_id'] ?? null);
        if (!$unit) {
            return null;
        }
        $limit = max(1, (int) $unit->concurrent_usage);
        [$startAt, $endAt] = $this->period($data);

        // the periods to check: the reservation + its repeats (create only)
        $periods = [[$startAt, $endAt]];
        $type = SubscriptionType::find($data['subscription_type_id'] ?? null);
        if (!$ignoreId && !empty($data['is_repeat']) && $type?->can_repeat && !empty($data['repeat_until'])) {
            $duration = $endAt ? $startAt->diffInSeconds($endAt) : null;
            foreach (Reservation::repeatStarts($startAt, $data['repeat_frequency'] ?? 'monthly', (int) ($data['repeat_interval'] ?? 1), Carbon::parse($data['repeat_until']), Reservation::MAX_REPEATS) as $start) {
                $periods[] = [$start, $duration !== null ? $start->copy()->addSeconds($duration) : null];
            }
        }

        foreach ($periods as $index => [$start, $end]) {
            $overlapping = Reservation::overlapping($unit->id, $start, $end)
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->with('member', 'plan')->orderBy('start_at')->get();
            // the busiest moment of the period: full when it already has "concurrent usage" reservations at the same time
            $busiest = $this->busiestMoment($overlapping, $start, $end);
            if (count($busiest) >= $limit) {
                // HTML (shown as HTML by custom.js for the "html_" errors): every text is escaped, the reservations are links to their edit page (new tab)
                $area = request()->routeIs('employee.*') ? 'employee' : 'account';
                $repeat = $index ? ' for the repeat of ' . e($start->format(Reservation::DATE_FORMAT)) : '';
                $taken = collect($busiest)->map(fn ($reservation) => '<a href="' . e(route($area . '.reservations.edit', $reservation->id)) . '" target="_blank" rel="noopener" class="font-medium underline">'
                    . e($reservation->period . ' (' . ($reservation->member?->name ?? '-') . ')') . '</a>')->join(', ');
                return $limit > 1
                    ? 'The unit "' . e($unit->name) . '" is full' . $repeat . ': it can be used by ' . $limit . ' reservations at the same time, reserved by ' . $taken . '.'
                    : 'The unit "' . e($unit->name) . '" is already reserved' . $repeat . ': ' . $taken . '.';
            }
        }

        return null;
    }

    // the reservations at the same time at the busiest moment inside start -> end (end null = continues):
    // each one counts from its start to its end inside the period, one ending when another starts is not at the same time
    private function busiestMoment($reservations, $start, $end): array
    {
        $events = [];
        foreach ($reservations as $reservation) {
            $from = max($reservation->start_at->getTimestamp(), $start->getTimestamp());
            $to = min($reservation->end_at ? $reservation->end_at->getTimestamp() : PHP_INT_MAX, $end ? $end->getTimestamp() : PHP_INT_MAX);
            if ($from < $to) {
                $events[] = [$from, 1, $reservation];
                $events[] = [$to, -1, $reservation];
            }
        }
        // by time; at the same time the ends first (back to back is not at the same time)
        usort($events, fn ($a, $b) => [$a[0], $a[1]] <=> [$b[0], $b[1]]);

        $now = [];
        $busiest = [];
        foreach ($events as [, $change, $reservation]) {
            if ($change > 0) {
                $now[$reservation->id] = $reservation;
                if (count($now) > count($busiest)) {
                    $busiest = $now;
                }
            } else {
                unset($now[$reservation->id]);
            }
        }

        return array_values($busiest);
    }

    private function reservationData($accountId, array $data): array
    {
        $plan = Plan::find($data['plan_id']);
        $timeBased = (bool) $plan?->is_time_based;
        // continue / repeat only when the subscription type allows them
        $type = SubscriptionType::find($data['subscription_type_id']);
        $isContinue = !empty($data['is_continue']) && $type?->auto_renew;
        $isRepeat = !empty($data['is_repeat']) && $type?->can_repeat;
        [$startAt, $endAt] = $this->period($data);
        // a time based plan: the plan amount x the periods from the start to the end (one period when it continues)
        if ($timeBased) {
            $data['amount'] = $endAt ? $plan->amountBetween($startAt, $endAt) : (float) $plan->amount;
        }

        // a package reservation is always for the package's member
        $packageId = $data['package_id'] ?? null;
        $memberId = $data['member_id'] ?? null;
        if ($packageId) {
            $memberId = Package::where('account_id', $accountId)->findOrFail($packageId)->member_id;
        }

        [$discountValue, $discountPercentage, $netAmount] = Reservation::calculate(
            (float) $data['amount'],
            isset($data['discount_percentage']) ? (float) $data['discount_percentage'] : null,
            isset($data['discount_value']) ? (float) $data['discount_value'] : null,
            $data['discount_type'] ?? 'percentage',
            isset($data['net_amount']) ? (float) $data['net_amount'] : null
        );

        return [
            'subscription_type_id' => $data['subscription_type_id'],
            'space_id' => $data['space_id'],
            'unit_id' => $data['unit_id'],
            'plan_id' => $data['plan_id'],
            // the columns are not nullable: 0 = none
            'package_id' => $packageId ?: 0,
            'member_id' => $memberId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'is_continue' => $isContinue,
            // the day of the month of the start (1 - 31)
            'subscription_day' => $startAt->day,
            'is_repeat' => $isRepeat,
            'repeat_frequency' => $isRepeat ? $data['repeat_frequency'] : 'monthly',
            'repeat_interval' => $isRepeat ? (int) $data['repeat_interval'] : 1,
            // a unit for 1 person is always 1 person (the form locks it too)
            'number_of_peoples' => Unit::find($data['unit_id'])?->capacity == 1 ? 1 : $data['number_of_peoples'],
            'amount' => round((float) $data['amount'], 2),
            'discount_percentage' => $discountPercentage,
            'discount_value' => $discountValue,
            'net_amount' => $netAmount,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
