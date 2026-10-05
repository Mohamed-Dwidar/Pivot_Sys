<?php

namespace Modules\AccountModule\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AccountModule\app\Http\Requests\RegisterAccountRequest;
use Modules\AccountModule\app\Http\Requests\UpdateAccountProfileRequest;
use Modules\AccountModule\app\Services\AccountService;

class AccountModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /* ---------- Guest ---------- */

    public function register()
    {
        return view('accountmodule::Guest.register');
    }

    public function registerPost(RegisterAccountRequest $request)
    {
        $this->accountService->register($request->validated());

        return redirect()->route('login')
            ->with('success', 'Your account has been registered successfully and is waiting for the admin approval.');
    }

    /* ---------- Logged in account ---------- */

    public function editProfile()
    {
        $account = Auth::user()->userable;
        return view('accountmodule::Account.profile', compact('account'));
    }

    public function updateProfile(UpdateAccountProfileRequest $request)
    {
        $this->accountService->updateProfile(Auth::user()->userable_id, $request->validated());

        return redirect()->route('account.profile.edit')->with('success', 'Your profile has been updated successfully.');
    }
}
