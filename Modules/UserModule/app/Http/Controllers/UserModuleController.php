<?php

namespace Modules\UserModule\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\UserModule\app\Http\Requests\LoginUserRequest;
use Modules\UserModule\app\Http\Requests\UpdateUserAccountRequest;
use Modules\UserModule\app\Services\UserService;

class UserModuleController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    // one login page for every user type except admins
    public function loginForm()
    {
        return view('usermodule::user.login');
    }

    public function login(LoginUserRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
        }

        $userable = Auth::user()->userable;
        $error = $userable ? $userable->loginError() : 'Your account is no longer available, please contact the administrator.';
        if ($error) {
            Auth::logout();
            return back()->withErrors(['email' => $error])->onlyInput('email');
        }

        $request->session()->regenerate();
        return redirect()->intended(route($userable->homeRoute()));
    }

    public function logout(Request $request)
    {
        // Only the web guard is logged out, so an admin session in the same browser survives.
        Auth::guard('web')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // logged in user (any type): change his email / password
    public function editAccount()
    {
        $user = Auth::user();
        $layout = $user->userable->layout();
        return view('usermodule::user.account', compact('user', 'layout'));
    }

    public function updateAccount(UpdateUserAccountRequest $request)
    {
        $this->userService->update(Auth::id(), $request->validated());

        return redirect()->route('user.account.edit')->with('success', 'Your login details have been updated successfully.');
    }
}
