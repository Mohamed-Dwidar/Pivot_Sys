<?php

namespace Modules\AccountModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AccountModule\app\Http\Requests\UpdateAccountProfileRequest;
use Modules\AccountModule\app\Services\AccountService;

// logged in account: complete / edit his profile
class AccountAccountModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

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
