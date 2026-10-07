<?php

namespace Modules\PackageModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Modules\PackageModule\app\Http\Controllers\Concerns\PackageActions;

// logged in account: manage the account's packages (the actions are in PackageActions)
class PackageAccountModuleController extends Controller
{
    use PackageActions;

    protected function area(): string
    {
        return 'account';
    }
}
