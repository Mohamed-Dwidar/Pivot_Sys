<?php

namespace Modules\AccountModule\app\Http\Controllers\Guest;

use Illuminate\Routing\Controller;
use Modules\AccountModule\app\Http\Requests\RegisterAccountRequest;
use Modules\AccountModule\app\Services\AccountService;

// guest: register a new account (pending until the admin approval)
class AccountGuestModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

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
}
