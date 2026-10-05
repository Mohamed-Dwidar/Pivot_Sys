<?php

namespace Modules\AccountModule\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\AccountModule\app\Http\Requests\AccountRequest;
use Modules\AccountModule\app\Models\Account;
use Modules\AccountModule\app\Services\AccountService;

class AccountAdminModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'status');
        $accounts = $this->accountService->paginate($filters);
        $counts = $this->accountService->countByStatus();
        $deletedCount = $this->accountService->countDeleted();

        return view('accountmodule::Admin.index', compact('accounts', 'counts', 'deletedCount', 'filters'));
    }

    public function create()
    {
        return view('accountmodule::Admin.create');
    }

    public function store(AccountRequest $request)
    {
        $account = $this->accountService->create($request->validated());

        return redirect()->route('admin.accounts.show', $account->id)->with('success', 'The account has been created successfully.');
    }

    public function show($id)
    {
        $account = $this->accountService->findOne($id);
        return view('accountmodule::Admin.show', compact('account'));
    }

    public function edit($id)
    {
        $account = $this->accountService->findOne($id);
        return view('accountmodule::Admin.edit', compact('account'));
    }

    public function update(AccountRequest $request, $id)
    {
        $this->accountService->update($id, $request->validated());

        return redirect()->route('admin.accounts.show', $id)->with('success', 'The account has been updated successfully.');
    }

    // approve / reject / activate / deactivate
    public function changeStatus(Request $request, $id)
    {
        $request->validate(['status' => ['required', Rule::in(array_keys(Account::STATUSES))]]);

        $this->accountService->changeStatus($id, $request->status);

        $messages = [
            Account::STATUS_ACTIVE => 'The account has been activated.',
            Account::STATUS_INACTIVE => 'The account has been deactivated.',
            Account::STATUS_REJECTED => 'The account request has been rejected.',
            Account::STATUS_PENDING => 'The account has been moved back to pending.',
        ];

        return back()->with('success', $messages[$request->status]);
    }

    public function destroy($id)
    {
        $this->accountService->deleteOne($id);

        return redirect()->route('admin.accounts.index')->with('success', 'The account has been deleted successfully.');
    }

    public function restore($id)
    {
        $this->accountService->restore($id);

        return redirect()->route('admin.accounts.show', $id)->with('success', 'The account has been restored successfully.');
    }
}
