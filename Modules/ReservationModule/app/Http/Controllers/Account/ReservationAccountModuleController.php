<?php

namespace Modules\ReservationModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\ReservationActions;

// logged in account: manage the account's reservations (the actions are in ReservationActions)
class ReservationAccountModuleController extends Controller
{
    use ReservationActions;

    protected function area(): string
    {
        return 'account';
    }
}
