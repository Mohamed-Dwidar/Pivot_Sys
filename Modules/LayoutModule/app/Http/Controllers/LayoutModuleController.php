<?php

namespace Modules\LayoutModule\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\AccountModule\app\Services\AccountService;

class LayoutModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function adminDashboard()
    {
        $counts = $this->accountService->countByStatus();
        $pendingAccounts = $this->accountService->latestPending();

        return view('layoutmodule::admin.dashboard', compact('counts', 'pendingAccounts'));
    }

    public function accountDashboard()
    {
        $account = Auth::user()->userable;
        return view('layoutmodule::account.dashboard', compact('account'));
    }
}
