<?php

namespace Modules\PackageModule\app\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\PackageModule\app\Models\Package;
use Modules\PackageModule\app\Repositories\PackageRepository;

/**
 * Every method takes the owner account id (the account, or the account of the logged in employee).
 */
class PackageService
{
    private $packageRepository;

    public function __construct(PackageRepository $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->packageRepository->forAccount($accountId)->filter($filters);
    }

    // the account's packages for the reservation form (no active / inactive since 2026-10-21): [id => ['name', 'member_id']]
    public function options($accountId, $currentId = null): array
    {
        // not forAccount(): its reservations count (a subquery per package) is not needed here and made the form take ~3 s
        return Package::with('member')->where('account_id', $accountId)
            // newest first (the reservation form, user 2026-10-22)
            ->orderByDesc('id')->get()
            ->mapWithKeys(fn ($package) => [$package->id => [
                'name' => $package->name . ' - ' . ($package->member?->name ?? '?'),
                'member_id' => $package->member_id,
                // the reservation form shows the package's member in its Member field (server search: the option is added)
                'member_text' => $package->member ? $package->member->name . ' ' . $package->member->phone : '',
            ]])->all();
    }

    // 404 when the package belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->packageRepository->forAccount($accountId)->findOrFail($id);
    }

    /**
     * The customer package statement (PDF, PackageActions::statement()) — not a tax invoice (those come from ETA):
     * ALL its reservations by date — the cancelled like ones are listed too (red, 0 hours, 0 amount, not in the totals) —,
     * hours of the TIMED counted ones only (not a full day / contract type,
     * end after start — the same rule as the list), the extras of type "extra" (adjustments = one "balance carried over" line),
     * the payments (refunds minus, withholding apart; no collector) and the totals.
     */
    public function statement($accountId, $id): array
    {
        $package = $this->findOne($accountId, $id)->load('member.company', 'account');
        $all = $package->reservations()
            ->with('space', 'unit', 'plan', 'subscriptionType', 'status')->orderBy('start_at')->orderBy('id')->get();
        // the counted ones give the count / hours / totals; a not counted (cancelled ...) one = 0 hours
        $reservations = $all->filter(fn ($reservation) => $reservation->isCounted())->values();
        $minutes = $all->mapWithKeys(fn ($reservation) => [$reservation->id => $reservation->isCounted() ? $this->timedMinutes($reservation) : 0]);
        $extras = $package->extrasList();

        return [
            'package' => $package,
            'account' => $package->account,
            'reservations' => $reservations,
            'allReservations' => $all,
            'cancelledCount' => $all->count() - $reservations->count(),
            'minutes' => $minutes,
            'hours' => round($minutes->sum() / 60, 2),
            'reservationsTotal' => round((float) $reservations->sum('net_amount'), 2),
            'extras' => $extras->where('type', 'extra')->values(),
            'extrasTotal' => round((float) $extras->where('type', 'extra')->sum('amount'), 2),
            'adjustments' => $extras->where('type', 'adjustment'),
            'adjustmentsTotal' => round((float) $extras->where('type', 'adjustment')->sum('amount'), 2),
            'payments' => $package->payments()->with('paymentMethod')->orderBy('paid_at')->orderBy('payment_no')->get(),
            'due' => round($package->payableAmount(), 2),
        ];
    }

    // the booked minutes of a timed reservation (0: a full day / contract type, or no end after the start)
    private function timedMinutes($reservation): int
    {
        $type = $reservation->subscriptionType;
        if ($type?->isFullDay() || $type?->is_contract || !$reservation->start_at || !$reservation->end_at || $reservation->end_at->lte($reservation->start_at)) {
            return 0;
        }

        return (int) ceil($reservation->start_at->diffInSeconds($reservation->end_at) / 60);
    }

    // $creator: the logged in user who adds the package
    /**
     * Add Package: the name suggested for a member (2026-10-22): "<member name> - <m-Y>" (this month), or when the account
     * already has a package with that name "<member name> - <m-Y> - N" (the first free N from 1). Null: not one of its members.
     */
    public function suggestName($accountId, $memberId): ?string
    {
        $member = \Modules\MemberModule\app\Models\Member::where('account_id', $accountId)->find((int) $memberId);
        if (!$member) {
            return null;
        }
        $base = trim($member->name) . ' - ' . now()->format('m-Y');
        $taken = Package::where('account_id', $accountId)
            ->where(fn ($query) => $query->where('name', $base)->orWhere('name', 'like', addcslashes($base, '%_\\') . ' - %'))
            ->pluck('name')->map(fn ($name) => mb_strtolower($name))->all();
        if (!in_array(mb_strtolower($base), $taken, true)) {
            return $base;
        }
        for ($number = 1; in_array(mb_strtolower($base . ' - ' . $number), $taken, true); $number++);

        return $base . ' - ' . $number;
    }

    public function create($accountId, array $data, Model $creator)
    {
        $package = $this->packageRepository->makeModel()->newInstance(['account_id' => $accountId]);
        $package->fill($this->packageData($package, $data));
        $package->creatable()->associate($creator);
        $package->save();
        $package->recalculate();

        return $package;
    }

    public function update($accountId, $id, array $data)
    {
        $package = $this->findOne($accountId, $id);
        $package->fill($this->packageData($package, $data));
        $package->recalculate();
        return $package;
    }

    // soft delete; the reason when it can not be deleted (it has reservations / payments), null when deleted
    public function deleteOne($accountId, $id): ?string
    {
        $package = $this->findOne($accountId, $id);
        if ($package->reservations_count > 0) {
            return 'This package has reservations, it can not be deleted. You can deactivate it.';
        }
        // its money must not be lost
        if ($package->payments()->exists()) {
            return 'This package has cash in, it can not be deleted. Refund or delete its cash in first.';
        }
        $package->delete();
        return null;
    }

    // after a reservation of the package is saved / deleted
    public function recalculate($packageId)
    {
        Package::find($packageId)?->recalculate();
    }

    // the price is the reservations total (not typed); the discount typed as % or as the net is saved as a %
    private function packageData(Package $package, array $data): array
    {
        [$discount] = Package::calculate(
            $package->reservationsTotal(),
            isset($data['discount_percentage']) ? (float) $data['discount_percentage'] : null,
            isset($data['after_discount']) ? (float) $data['after_discount'] : null,
            $data['discount_type'] ?? 'percentage'
        );

        return [
            'name' => $data['name'],
            'member_id' => $data['member_id'],
            'discount_percentage' => $discount,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
