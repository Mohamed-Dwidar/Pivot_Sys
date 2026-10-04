<?php


namespace Modules\AdminModule\app\Http\Controllers\Auth;

// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Auth;

class AdminAuthController extends Controller
{
    function index()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/admin/dashboard');
        } else {
            return view('adminmodule::login');
        }
    }

    function login(Request $request)
    {
        $rememberme = request()->has('rememberme') ? 1 : 0;

        if (auth('admin')->attempt(
            [
                'email' => $request->email,
                'password' => $request->password
            ],
            $rememberme
        )) {
            return redirect()->intended('admin');
        }
        return redirect()->back()->withErrors(['error' => 'The email or password is incorrect']);
    }

    function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect()->to('admin');
    }
}
