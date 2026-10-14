<?php

namespace Modules\ReservationModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\TodayActions;

// logged in account: the Today page (the actions are in TodayActions)
class TodayAccountModuleController extends Controller
{
    use TodayActions;

    protected function area(): string
    {
        return 'account';
    }
}
