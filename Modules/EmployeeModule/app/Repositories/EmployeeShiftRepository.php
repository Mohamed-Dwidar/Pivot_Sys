<?php

namespace Modules\EmployeeModule\app\Repositories;

use Modules\EmployeeModule\app\Models\EmployeeShift;
use Prettus\Repository\Eloquent\BaseRepository;

class EmployeeShiftRepository extends BaseRepository
{
    public function model()
    {
        return EmployeeShift::class;
    }

    // shifts of one account only
    public function forAccount($accountId)
    {
        return EmployeeShift::withCount('employees')->where('account_id', $accountId);
    }
}
