<?php

namespace Modules\SpaceModule\app\Services;

use Illuminate\Support\Facades\DB;
use Modules\SpaceModule\app\Models\SubscriptionType;
use Modules\SpaceModule\app\Repositories\SubscriptionTypeRepository;
use Modules\UnitModule\app\Services\UnitService;

/**
 * Every method takes the owner account id, so an account can only reach its own subscription types.
 */
class SubscriptionTypeService
{
    private $subscriptionTypeRepository;
    private $unitService;

    public function __construct(SubscriptionTypeRepository $subscriptionTypeRepository, UnitService $unitService)
    {
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
        $this->unitService = $unitService;
    }

    public function paginate($accountId, array $filters = [], $perPage = 15)
    {
        return $this->subscriptionTypeRepository->forAccount($accountId)->filter($filters)->orderBy('name')->paginate($perPage)->withQueryString();
    }

    // [id => name], for the spaces form / filter
    public function options($accountId): array
    {
        return $this->subscriptionTypeRepository->forAccount($accountId)->orderBy('name')->pluck('name', 'id')->all();
    }

    // 404 when the subscription type belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->subscriptionTypeRepository->forAccount($accountId)->with(['spaces' => fn ($query) => $query->orderByLocalized('name')->latest('id')])->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        return $this->subscriptionTypeRepository->create($this->typeData($data) + ['account_id' => $accountId]);
    }

    public function update($accountId, $id, array $data)
    {
        $subscriptionType = $this->findOne($accountId, $id);
        $subscriptionType->update($this->typeData($data));
        return $subscriptionType;
    }

    // also removes it from the spaces it was assigned to and from their units
    public function deleteOne($accountId, $id)
    {
        $subscriptionType = $this->findOne($accountId, $id);

        return DB::transaction(function () use ($subscriptionType) {
            $subscriptionType->spaces()->detach();
            $this->unitService->clearSubscriptionType($subscriptionType->id);
            return $subscriptionType->delete();
        });
    }

    private function typeData(array $data): array
    {
        $type_data = ['name' => $data['name']];
        foreach (array_keys(SubscriptionType::OPTIONS) as $option) {
            $type_data[$option] = !empty($data[$option]);
        }
        return $type_data;
    }
}
