<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\PaymentModule\app\Http\Controllers\Concerns\MyShiftActions;

// the account's own shift (its own payments, refunds included): the same page as the employees', no reminders / logout popup
class MyShiftAccountModuleController extends Controller
{
    use MyShiftActions;

    protected function area(): string
    {
        return 'account';
    }
}
