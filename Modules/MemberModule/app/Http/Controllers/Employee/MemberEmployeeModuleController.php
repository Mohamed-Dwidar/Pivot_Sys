<?php

namespace Modules\MemberModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Modules\MemberModule\app\Http\Controllers\Concerns\MemberActions;

// logged in employee: the account's members — list, view, add, edit (MemberActions); no delete
class MemberEmployeeModuleController extends Controller
{
    use MemberActions;

    protected function area(): string
    {
        return 'employee';
    }
}
