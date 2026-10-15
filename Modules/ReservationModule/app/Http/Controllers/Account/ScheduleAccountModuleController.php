<?php

namespace Modules\ReservationModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\ScheduleActions;

// logged in account: the Schedule page (the actions are in ScheduleActions)
class ScheduleAccountModuleController extends Controller
{
    use ScheduleActions;

    protected function area(): string
    {
        return 'account';
    }
}
