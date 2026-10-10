<?php

namespace Modules\PaymentModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\PaymentModule\app\Http\Controllers\Concerns\PaymentActions;

// logged in employee: add / view the account's payments (the actions are in PaymentActions)
class PaymentEmployeeModuleController extends Controller
{
    use PaymentActions;

    protected function area(): string
    {
        return 'employee';
    }
}
