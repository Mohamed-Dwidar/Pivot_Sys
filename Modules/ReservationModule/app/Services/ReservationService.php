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

    private function reservationData($accountId, array $data): array
    {
        // a time based plan: the date + the time, else the whole days
        $plan = Plan::find($data['plan_id']);
        $timeBased = (bool) $plan?->is_time_based;
        $startAt = $timeBased && !empty($data['start_time'])
            ? Carbon::parse($data['start_at'] . ' ' . $data['start_time'])
            : Carbon::parse($data['start_at'])->startOfDay();
        // continue / repeat only when the subscription type allows them
        $type = SubscriptionType::find($data['subscription_type_id']);
        $isContinue = !empty($data['is_continue']) && $type?->auto_renew;
        $isRepeat = !empty($data['is_repeat']) && $type?->can_repeat;
        // a continuous reservation has no end
        $endAt = $isContinue || empty($data['end_at']) ? null
            : ($timeBased && !empty($data['end_time']) ? Carbon::parse($data['end_at'] . ' ' . $data['end_time']) : Carbon::parse($data['end_at'])->endOfDay());
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
