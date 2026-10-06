<?php

namespace Modules\SpaceModule\app\Services;

use App\Helpers\UploaderHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Modules\SpaceModule\app\Models\Space;
use Modules\SpaceModule\app\Repositories\SpaceRepository;
use Modules\UnitModule\app\Services\UnitService;

/**
 * Every method takes the owner account id, so an account can only reach its own spaces.
 * Images are saved in public/uploads/spaces/{space_id}/.
 */
class SpaceService
{
    use UploaderHelper;

    private $spaceRepository;
    private $unitService;

    public function __construct(SpaceRepository $spaceRepository, UnitService $unitService)
    {
        $this->spaceRepository = $spaceRepository;
        $this->unitService = $unitService;
    }

    public function paginate($accountId, array $filters = [], $perPage = 15)
    {
        return $this->spaceRepository->forAccount($accountId)->filter($filters)->orderByLocalized('name')->latest('id')->paginate($perPage)->withQueryString();
    }

    // 404 when the space belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->spaceRepository->forAccount($accountId)->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        $space = DB::transaction(function () use ($accountId, $data) {
            $space = $this->spaceRepository->create($this->spaceData($data) + ['account_id' => $accountId]);
            $space->subscriptionTypes()->sync($data['subscription_types'] ?? []);
            return $space;
        });

        $this->storeImages($space, $data['images'] ?? []);
        return $space;
    }

    public function update($accountId, $id, array $data)
    {
        $space = $this->findOne($accountId, $id);

        DB::transaction(function () use ($space, $data) {
            $space->update($this->spaceData($data));
            $space->subscriptionTypes()->sync($data['subscription_types'] ?? []);
            $this->unitService->syncSpaceSubscriptionTypes($space->id, $data['subscription_types'] ?? []);
        });

        $this->deleteImages($space, $data['delete_images'] ?? []);
        $this->storeImages($space, $data['images'] ?? []);
        return $space;
    }

    // soft delete (the space and its units)
    public function deleteOne($accountId, $id)
    {
        $space = $this->findOne($accountId, $id);

        // soft delete: images, subscription types and units are kept so the space can be restored
        return DB::transaction(function () use ($space) {
            $this->unitService->deleteForSpace($space);
            return $space->delete();
        });
    }

    private function spaceData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'],
            'is_active' => !empty($data['is_active']),
        ];
    }

    private function storeImages(Space $space, array $files)
    {
        foreach ($files as $file) {
            $name = $this->uploadImage($file, 'spaces/' . $space->id, 'space', 1200, 1200);
            $space->images()->create(['name' => $name]);
        }
    }

    // $ids: images of this space to remove
    private function deleteImages(Space $space, array $ids)
    {
        foreach ($space->images()->whereIn('id', $ids)->get() as $image) {
            File::delete(public_path('uploads/spaces/' . $space->id . '/' . $image->name));
            $image->delete();
        }
    }
}
