<?php

namespace Modules\ReservationModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\TodayActions;

// logged in employee: the Today page (the actions are in TodayActions)
class TodayEmployeeModuleController extends Controller
{
    use TodayActions;

    protected function area(): string
    {
        return 'employee';
    }
}
