<?php

namespace Modules\PaymentModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\PaymentModule\app\Http\Controllers\Concerns\DayClosingActions;

// logged in employee: close the account's days (the actions are in DayClosingActions; reopen = the account only)
class DayClosingEmployeeModuleController extends Controller
{
    use DayClosingActions;

    protected function area(): string
    {
        return 'employee';
    }
}
