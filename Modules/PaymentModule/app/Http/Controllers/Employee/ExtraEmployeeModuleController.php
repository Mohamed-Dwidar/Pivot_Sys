<?php

namespace Modules\PaymentModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\PaymentModule\app\Http\Controllers\Concerns\ExtraActions;

// logged in employee: add an extra (a sale) to a package of the account, from the package view (ExtraActions)
class ExtraEmployeeModuleController extends Controller
{
    use ExtraActions;

    protected function area(): string
    {
        return 'employee';
    }
}
