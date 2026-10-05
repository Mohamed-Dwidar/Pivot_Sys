<?php

namespace Modules\JobModule\app\Repositories;

use Modules\JobModule\app\Models\Job;
use Prettus\Repository\Eloquent\BaseRepository;

class JobRepository extends BaseRepository
{
    public function model()
    {
        return Job::class;
    }

    // jobs of one account only
    public function forAccount($accountId)
    {
        return Job::where('account_id', $accountId);
    }
}
