<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\PaymentModule\app\Http\Controllers\Concerns\PaymentActions;

// logged in account: the account's payments (the actions are in PaymentActions; edit / delete / refund come here, step 6)
class PaymentAccountModuleController extends Controller
{
    use PaymentActions;

    protected function area(): string
    {
        return 'account';
    }
}
