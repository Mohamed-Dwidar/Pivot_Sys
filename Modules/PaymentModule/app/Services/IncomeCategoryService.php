<?php

namespace Modules\PaymentModule\app\Services;

use Modules\PaymentModule\app\Repositories\IncomeCategoryRepository;

/**
 * Every method takes the owner account id, so an account can only reach its own income categories.
 */
class IncomeCategoryService
{
    private $categoryRepository;

    public function __construct(IncomeCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    // list query (paged / ordered by DataTables)
    public function listQuery($accountId, array $filters = [])
    {
        return $this->categoryRepository->forAccount($accountId)->filter($filters);
    }

    // [id => name] for the payment form: the active ones + the payment's current one
    public function options($accountId, $currentId = null): array
    {
        return $this->categoryRepository->forAccount($accountId)
            ->where(fn ($query) => $query->where('is_active', true)->orWhere('id', (int) $currentId))
            ->ordered()->get()->pluck('name', 'id')->all();
    }

    // all of them, for the list filters
    public function filterOptions($accountId): array
    {
        return $this->categoryRepository->forAccount($accountId)->ordered()->get()->pluck('name', 'id')->all();
    }

    // 404 when it belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->categoryRepository->forAccount($accountId)->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        // no sort order: after the last one
        if (!isset($data['sort_order']) || $data['sort_order'] === '') {
            $data['sort_order'] = (int) $this->categoryRepository->forAccount($accountId)->max('sort_order') + 1;
        }

        return $this->categoryRepository->create($this->categoryData($data) + ['account_id' => $accountId]);
    }

    public function update($accountId, $id, array $data)
    {
        $category = $this->findOne($accountId, $id);
        $category->update($this->categoryData($data));
        return $category;
    }

    // the active switch of the list
    public function setActive($accountId, $id, bool $isActive)
    {
        $category = $this->findOne($accountId, $id);
        $category->update(['is_active' => $isActive]);
        return $category;
    }

    // soft delete; the reason when it can not be deleted (used by payments), null when deleted
    public function deleteOne($accountId, $id): ?string
    {
        $category = $this->findOne($accountId, $id);
        if ($category->payments_count > 0) {
            return 'This income category is used by payments, deactivate it instead.';
        }
        $category->delete();
        return null;
    }

    private function categoryData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
            'is_active' => !empty($data['is_active']),
            'is_revenue' => !empty($data['is_revenue']),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
