<?php

namespace Modules\JobModule\app\Services;

use Modules\JobModule\app\Repositories\JobRepository;

/**
 * Every method takes the owner account id, so an account can only reach its own jobs.
 */
class JobService
{
    private $jobRepository;

    public function __construct(JobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    public function paginate($accountId, array $filters = [], $perPage = 15)
    {
        return $this->jobRepository->forAccount($accountId)->filter($filters)->latest()->paginate($perPage)->withQueryString();
    }

    public function countForAccount($accountId): int
    {
        return $this->jobRepository->forAccount($accountId)->count();
    }

    // [id => name] of the account jobs, for select inputs
    public function options($accountId): array
    {
        return $this->jobRepository->forAccount($accountId)->orderBy('name_ar')->pluck('name_ar', 'id')->all();
    }

    // 404 when the job belongs to another account
    public function findOne($accountId, $id)
    {
        return $this->jobRepository->forAccount($accountId)->findOrFail($id);
    }

    public function create($accountId, array $data)
    {
        return $this->jobRepository->create($this->jobData($data) + ['account_id' => $accountId]);
    }

    public function update($accountId, $id, array $data)
    {
        $job = $this->findOne($accountId, $id);
        $job->update($this->jobData($data));
        return $job;
    }

    // soft delete
    public function deleteOne($accountId, $id)
    {
        return $this->findOne($accountId, $id)->delete();
    }

    private function jobData(array $data): array
    {
        return [
            'name_ar' => $data['name_ar'],
            'name_en' => $data['name_en'] ?? null,
        ];
    }
}
