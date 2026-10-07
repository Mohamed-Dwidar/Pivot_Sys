<?php

namespace Modules\ReservationModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\ReservationActions;

// logged in employee: manage his account's reservations (the actions are in ReservationActions)
class ReservationEmployeeModuleController extends Controller
{
    use ReservationActions;

    protected function area(): string
    {
        return 'employee';
    }
}
