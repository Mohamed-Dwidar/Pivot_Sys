<?php

namespace Modules\PackageModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\PackageModule\app\Http\Controllers\Concerns\PackageActions;

// logged in employee: manage his account's packages (the actions are in PackageActions)
class PackageEmployeeModuleController extends Controller
{
    use PackageActions;

    protected function area(): string
    {
        return 'employee';
    }
}
