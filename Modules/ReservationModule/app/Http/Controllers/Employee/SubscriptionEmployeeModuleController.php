<?php

namespace Modules\ReservationModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\ReservationModule\app\Http\Controllers\Concerns\SubscriptionActions;

// logged in employee: the Subscriptions page (the actions are in SubscriptionActions)
class SubscriptionEmployeeModuleController extends Controller
{
    use SubscriptionActions;

    protected function area(): string
    {
        return 'employee';
    }
}
