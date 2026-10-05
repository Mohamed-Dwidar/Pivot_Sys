<?php

namespace Modules\LayoutModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LayoutAccountModuleController extends Controller
{
    public function dashboard()
    {
        $account = Auth::user()->userable;
        return view('layoutmodule::account.dashboard', compact('account'));
    }
}
