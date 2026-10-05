<?php

namespace Modules\AdminModule\app\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AdminModule\app\Http\Requests\LoginAdminRequest;

class AdminAuthController extends Controller
{
    public function index()
    {
        return view('adminmodule::login');
    }

    public function login(LoginAdminRequest $request)
    {
        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('rememberme'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Only the admin guard is logged out, so an account session in the same browser survives.
        Auth::guard('admin')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
