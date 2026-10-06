<?php

namespace Modules\UnitModule\app\Services;

use App\Helpers\UploaderHelper;
use Illuminate\Support\Facades\File;
use Modules\SpaceModule\app\Models\Space;
use Modules\UnitModule\app\Models\Unit;
use Modules\UnitModule\app\Repositories\UnitRepository;

/**
 * Units always belong to a space of the logged in account.
 * Images are saved in public/uploads/units/{unit_id}/.
 */
class UnitService
{
    use UploaderHelper;

    private $unitRepository;

    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    public function listForSpace(Space $space)
    {
        return $this->unitRepository->forSpace($space->account_id, $space->id)->orderByLocalized('name')->latest('id')->get();
    }

    // 404 when the unit is not in this space of this account
    public function findOne(Space $space, $id)
    {
        return $this->unitRepository->forSpace($space->account_id, $space->id)->findOrFail($id);
    }

    public function create(Space $space, array $data)
    {
        $unit = $this->unitRepository->create($this->unitData($data) + [
            'account_id' => $space->account_id,
            'space_id' => $space->id,
        ]);

        $this->storeImages($unit, $data['images'] ?? []);
        return $unit;
    }

    public function update(Space $space, $id, array $data)
    {
        $unit = $this->findOne($space, $id);
        $unit->update($this->unitData($data));

        $this->deleteImages($unit, $data['delete_images'] ?? []);
        $this->storeImages($unit, $data['images'] ?? []);
        return $unit;
    }

    // soft delete: the images are kept so the unit can be restored
    public function deleteOne(Space $space, $id)
    {
        return $this->findOne($space, $id)->delete();
    }

    // when the space is deleted (soft delete)
    public function deleteForSpace(Space $space)
    {
        Unit::where('space_id', $space->id)->delete();
    }

    // units keep only subscription types that are still assigned to their space
    public function syncSpaceSubscriptionTypes($spaceId, array $subscriptionTypeIds)
    {
        Unit::withTrashed()->where('space_id', $spaceId)
            ->whereNotIn('subscription_type_id', array_merge([0], $subscriptionTypeIds))
            ->update(['subscription_type_id' => 0]);
    }

    // when the subscription type is deleted
    public function clearSubscriptionType($subscriptionTypeId)
    {
        Unit::withTrashed()->where('subscription_type_id', $subscriptionTypeId)->update(['subscription_type_id' => 0]);
    }

    private function unitData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            // the column is not nullable: 0 = none
            'subscription_type_id' => $data['subscription_type_id'] ?? 0,
            'color_id' => $data['color_id'],
            'description_ar' => $data['description_ar'] ?? null,
            'description_en' => $data['description_en'] ?? null,
            'notes_ar' => $data['notes_ar'] ?? null,
            'notes_en' => $data['notes_en'] ?? null,
            'capacity' => $data['capacity'],
            'concurrent_usage' => $data['concurrent_usage'],
            'lease_period' => $data['lease_period'] ?? null,
            'is_active' => !empty($data['is_active']),
        ];
    }

    private function storeImages(Unit $unit, array $files)
    {
        foreach ($files as $file) {
            $name = $this->uploadImage($file, 'units/' . $unit->id, 'unit', 1200, 1200);
            $unit->images()->create(['name' => $name]);
        }
    }

    // $ids: images of this unit to remove
    private function deleteImages(Unit $unit, array $ids)
    {
        foreach ($unit->images()->whereIn('id', $ids)->get() as $image) {
            File::delete(public_path('uploads/units/' . $unit->id . '/' . $image->name));
            $image->delete();
        }
    }
}
