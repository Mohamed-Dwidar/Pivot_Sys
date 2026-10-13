<?php

namespace Modules\ReservationModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\PackageModule\app\Models\Package;
use Modules\PackageModule\app\Services\PackageService;
use Modules\PaymentModule\app\Services\PaymentService;
use Modules\ReservationModule\app\Models\Reservation;
use Modules\ReservationModule\app\Models\ReservationStatus;
use Modules\ReservationModule\app\Repositories\ReservationRepository;
use Modules\PlanModule\app\Models\Plan;
use Modules\SpaceModule\app\Models\SubscriptionType;
use Modules\UnitModule\app\Models\Unit;

/**
 * Every method takes the owner account id (the account, or the account of the logged in employee).
 * The amounts are calculated here again (the form calculates them while typing), and the package totals are kept up to date,
 * so is the reservation's paid / remaining (PaymentService::refresh(): its net, or 0 inside a package / with a not counted status).
 */
class ReservationService
{
    private $reservationRepository;
    private $packageService;
    private $statusService;
    private $paymentService;

    public function __construct(ReservationRepository $reservationRepository, PackageService $packageService, ReservationStatusService $statusService, PaymentService $paymentService)
    {
        $this->reservationRepository = $reservationRepository;
        $this->packageService = $packageService;
        $this->statusService = $statusService;
        $this->paymentService = $paymentService;
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
            // remaining = its net (the copies too)
            foreach ($reservation->repeats()->get()->prepend($reservation) as $saved) {
                $this->paymentService->refresh($saved);
            }
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

            // moved into a package (it was paid on its own): its payments go to the package (paid through it now)
            if (!$oldPackageId && $reservation->package_id && ($package = $reservation->load('package')->package)) {
                $this->paymentService->movePayments($reservation, $package);
            }

            // the old and the new package (it can be changed)
            $this->packageService->recalculate($oldPackageId);
            if ($reservation->package_id != $oldPackageId) {
                $this->packageService->recalculate($reservation->package_id);
            }
            // the net / status / package may have changed
            $this->paymentService->refresh($reservation->load('status'));
            return $reservation;
        });
    }

    // the status menu of the reservation view; the package is calculated again (a not counted status is not in its price).
    // Not counted → counted (cancelled → confirmed ...): the slot must be free again, the same check as a new booking
    public function changeStatus($accountId, $id, $statusId)
    {
        $reservation = $this->findOne($accountId, $id);
        $status = ReservationStatus::where('account_id', $accountId)->find($statusId);
        if ($status?->is_counted && !$reservation->isCounted() && ($error = $this->restoreError($reservation))) {
            throw ValidationException::withMessages(['html_status' => $error]);
        }

        return DB::transaction(function () use ($reservation, $statusId) {
            $reservation->update(['reservation_status_id' => $statusId]);
            $this->packageService->recalculate($reservation->package_id);
            // a not counted status (cancelled ...): nothing is due
            $this->paymentService->refresh($reservation->load('status'));
            return $reservation;
        });
    }

    /**
     * Restore a not counted reservation (cancelled, pending ...) to the default counted status (Confirmed):
     * refused when its slot is taken now (the reservations holding it, as links); then like changeStatus()
     * (package recalculated, paid / remaining refreshed: the amount due comes back).
     */
    public function restore($accountId, $id)
    {
        $reservation = $this->findOne($accountId, $id);
        if ($reservation->isCounted()) {
            throw ValidationException::withMessages(['html_status' => 'This reservation is already active.']);
        }
        $status = $this->restoreStatus($accountId)
            ?? throw ValidationException::withMessages(['html_status' => 'There is no active counted status to restore to.']);

        return $this->changeStatus($accountId, $id, $status->id);
    }

    // the status a reservation is restored to: the default one when it is counted, else the first active counted one
    public function restoreStatus($accountId): ?ReservationStatus
    {
        $statuses = ReservationStatus::where('account_id', $accountId)->where('is_active', true)->where('is_counted', true);
        return (clone $statuses)->where('is_default', true)->first() ?? $statuses->ordered()->first();
    }

    // the slot of a not counted reservation is free for it again (null) / taken: the reason (HTML, links to the holders)
    public function restoreError(Reservation $reservation): ?string
    {
        $unit = Unit::find($reservation->unit_id);
        // a continuous subscription has no end (as when it is saved, period())
        $end = $reservation->is_continue && $reservation->subscriptionType?->auto_renew ? null : $reservation->end_at;

        return $unit ? $this->periodsError($unit, [[$reservation->start_at, $end]], $reservation->id) : null;
    }

    /**
     * The next period of a subscription (the "Renew" form, not saved): same member, type, space, unit, plan, amounts,
     * starting when it ends (date only plans: the next day) for the same length; no package / notes.
     */
    public function renewalOf(Reservation $subscription): Reservation
    {
        $end = $subscription->realEnd() ?? $subscription->start_at;
        $start = $subscription->plan?->is_time_based ? $end->copy() : $end->copy()->addSecond()->startOfDay();
        $length = $subscription->start_at->diffInSeconds($end);

        $renewal = $subscription->replicate(['paid_amount', 'remaining', 'repeat_id', 'notes']);
        $renewal->forceFill([
            'start_at' => $start,
            'end_at' => $length > 0 ? $start->copy()->addSeconds($length) : $start->copy(),
            'package_id' => 0,
            'notes' => null,
        ]);

        return $renewal;
    }

    // soft delete; the reason when it can not be deleted (it has payments: their money must not be lost), null when deleted
    public function deleteOne($accountId, $id): ?string
    {
        $reservation = $this->findOne($accountId, $id);
        if ($reservation->payments()->exists()) {
            return 'This reservation has payments, it can not be deleted. Refund or delete its payments first, or move it into a package (its payments move with it).';
        }

        DB::transaction(function () use ($reservation) {
            $reservation->delete();
            $this->packageService->recalculate($reservation->package_id);
        });
        return null;
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

        return $this->periodsError($unit, $periods, $ignoreId);
    }

    // the first period (the reservation, then its repeats) where the unit is full: the reason (HTML), null when all are free
    private function periodsError(Unit $unit, array $periods, $ignoreId = null): ?string
    {
        $limit = max(1, (int) $unit->concurrent_usage);
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
