<?php

namespace Modules\EmployeeModule\app\Repositories;

use Modules\EmployeeModule\app\Models\EmployeePosition;
use Prettus\Repository\Eloquent\BaseRepository;

class EmployeePositionRepository extends BaseRepository
{
    public function model()
    {
        return EmployeePosition::class;
    }

    // positions of one account only
    public function forAccount($accountId)
    {
        return EmployeePosition::withCount('employees')->where('account_id', $accountId);
    }
}
