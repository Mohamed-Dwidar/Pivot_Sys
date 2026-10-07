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

    // the active packages (+ $currentId even if inactive) for the reservation form: [id => ['name', 'member_id']]
    public function options($accountId, $currentId = null): array
    {
        return $this->packageRepository->forAccount($accountId)
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $currentId))
            ->orderBy('name')->get()
            ->mapWithKeys(fn ($package) => [$package->id => [
                'name' => $package->name . ' - ' . ($package->member?->name ?? '?'),
                'member_id' => $package->member_id,
            ]])->all();
    }

    // 404 when the package belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->packageRepository->forAccount($accountId)->findOrFail($id);
    }

    // $creator: the logged in user who adds the package
    public function create($accountId, array $data, Model $creator)
    {
        $package = $this->packageRepository->makeModel()->newInstance($this->packageData($data) + ['account_id' => $accountId]);
        $package->creatable()->associate($creator);
        $package->save();
        $package->recalculate();

        return $package;
    }

    public function update($accountId, $id, array $data)
    {
        $package = $this->findOne($accountId, $id);
        $package->fill($this->packageData($data));
        $package->recalculate();
        return $package;
    }

    // the active switch of the list
    public function setActive($accountId, $id, bool $isActive)
    {
        $package = $this->findOne($accountId, $id);
        $package->update(['is_active' => $isActive]);
        return $package;
    }

    // soft delete, false when it has reservations
    public function deleteOne($accountId, $id)
    {
        $package = $this->findOne($accountId, $id);
        if ($package->reservations_count > 0) {
            return false;
        }
        return $package->delete();
    }

    // after a reservation of the package is saved / deleted
    public function recalculate($packageId)
    {
        Package::find($packageId)?->recalculate();
    }

    private function packageData(array $data): array
    {
        $amount = (float) $data['amount'];
        [$discount, $afterDiscount] = Package::calculate(
            $amount,
            isset($data['discount_percentage']) ? (float) $data['discount_percentage'] : null,
            isset($data['after_discount']) ? (float) $data['after_discount'] : null,
            $data['discount_type'] ?? 'percentage'
        );

        return [
            'name' => $data['name'],
            'member_id' => $data['member_id'],
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
            'amount' => $amount,
            'discount_percentage' => $discount,
            'after_discount' => $afterDiscount,
            'is_active' => !empty($data['is_active']),
            'notes' => $data['notes'] ?? null,
        ];
    }
}
