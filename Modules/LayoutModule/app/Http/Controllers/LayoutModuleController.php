<?php

namespace Modules\LayoutModule\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LayoutModuleController extends Controller
{
    public function admin_dashboard()
    {
        if (Auth::guard('admin')->check()) {
            return view('layoutmodule::dashboard');
        } else {
            return redirect()->route('admin.login');
        }
    }
}
