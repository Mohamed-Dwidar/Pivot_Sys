<?php

namespace Modules\PaymentModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\PaymentModule\app\Http\Controllers\Concerns\ExpenseActions;

// logged in employee: record expenses (into their open shift) and see their own, one day at a time (ExpenseActions); no edit / delete
class ExpenseEmployeeModuleController extends Controller
{
    use ExpenseActions;

    protected function area(): string
    {
        return 'employee';
    }
}
