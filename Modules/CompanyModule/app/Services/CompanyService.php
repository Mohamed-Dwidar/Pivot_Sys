<?php

namespace Modules\CompanyModule\app\Services;

use Modules\CompanyModule\app\Repositories\CompanyRepository;

/**
 * Every method takes the owner account id, so an account can only reach its own companies.
 */
class CompanyService
{
    private $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function paginate($accountId, array $filters = [], $perPage = 15)
    {
        return $this->companyRepository->forAccount($accountId)->filter($filters)->latest()->paginate($perPage)->withQueryString();
    }

    public function countForAccount($accountId): int
    {
        return $this->companyRepository->forAccount($accountId)->count();
    }

    // [id => name] of the account companys, for select inputs
    public function options($accountId): array
    {
        return $this->companyRepository->forAccount($accountId)->orderBy('name_ar')->pluck('name_ar', 'id')->all();
    }

    // 404 when the company belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->companyRepository->forAccount($accountId)->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        return $this->companyRepository->create($this->companyData($data) + ['account_id' => $accountId]);
    }

    public function update($accountId, $id, array $data)
    {
        $company = $this->findOne($accountId, $id);
        $company->update($this->companyData($data));
        return $company;
    }

    // soft delete
    public function deleteOne($accountId, $id)
    {
        return $this->findOne($accountId, $id)->delete();
    }

    private function companyData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
        ];
    }
}
