<?php

namespace Modules\PlanModule\app\Repositories;

use Modules\PlanModule\app\Models\Plan;
use Prettus\Repository\Eloquent\BaseRepository;

class PlanRepository extends BaseRepository
{
    public function model()
    {
        return Plan::class;
    }

    // plans of one account only
    public function forAccount($accountId)
    {
        return Plan::withCount('spaces', 'units')->where('account_id', $accountId);
    }
}
