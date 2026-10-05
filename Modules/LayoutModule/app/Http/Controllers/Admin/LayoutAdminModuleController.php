<?php

namespace Modules\LayoutModule\app\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\AccountModule\app\Services\AccountService;

class LayoutAdminModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function dashboard()
    {
        $counts = $this->accountService->countByStatus();
        $pendingAccounts = $this->accountService->latestPending();

        return view('layoutmodule::admin.dashboard', compact('counts', 'pendingAccounts'));
    }
}
