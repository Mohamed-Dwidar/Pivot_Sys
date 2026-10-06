<?php

namespace Modules\LayoutModule\app\Http\Controllers\Employee;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LayoutEmployeeModuleController extends Controller
{
    // the employee's own profile (read only, his account manages it)
    public function dashboard()
    {
        $employee = Auth::user()->userable->load('account', 'position', 'shift');
        return view('layoutmodule::employee.dashboard', compact('employee'));
    }
}
