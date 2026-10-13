<?php

namespace Modules\ReservationModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\SubscriptionActions;

// logged in account: the Subscriptions page (the actions are in SubscriptionActions)
class SubscriptionAccountModuleController extends Controller
{
    use SubscriptionActions;

    protected function area(): string
    {
        return 'account';
    }
}
