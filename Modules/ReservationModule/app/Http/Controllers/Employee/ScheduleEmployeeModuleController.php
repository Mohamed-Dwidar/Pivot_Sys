<?php

namespace Modules\ReservationModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\ScheduleActions;

// logged in employee: the Schedule page (the actions are in ScheduleActions)
class ScheduleEmployeeModuleController extends Controller
{
    use ScheduleActions;

    protected function area(): string
    {
        return 'employee';
    }
}
