<?php

namespace Modules\LayoutModule\app\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LayoutModuleController extends Controller
{


    public function __construct()
    {
        
    }

    public function home_page()
    {
        return view('layoutmodule::home');
    }

    public function admin_dashboard()
    {
        if (Auth::guard('admin')->check()) {
            // dd(Auth::guard('admin')->user()->email);
            return view('layoutmodule::dashboard');
        } else {
            return redirect()->route('admin.login');
        }
    }
}
