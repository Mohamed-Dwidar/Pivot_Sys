<?php

namespace Modules\AccountModule\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\AccountModule\app\Http\Requests\AccountRequest;
use Modules\AccountModule\app\Models\Account;
use Modules\AccountModule\app\Services\AccountService;
use Yajra\DataTables\Facades\DataTables;

class AccountAdminModuleController extends Controller
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    // the list page (status tabs + search), the rows come from data() (DataTables)
    public function index(Request $request)
    {
        $status = $request->input('status', '');
        $counts = $this->accountService->countByStatus();
        $deletedCount = $this->accountService->countDeleted();

        return view('accountmodule::Admin.index', compact('status', 'counts', 'deletedCount'));
    }

    // DataTables server side: search + status ('deleted' = soft deleted accounts)
    public function data(Request $request)
    {
        return DataTables::eloquent($this->accountService->listQuery())
            // inside filter() so "of N total" counts all the accounts
            ->filter(function ($query) use ($request) {
                $query->filter(['search' => trim((string) $request->input('search.value'))] + $request->only('status'));
            })
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('name_html', fn ($account) => $account->trashed()
                ? '<span class="font-medium">' . e($account->name) . '</span>'
                : '<a href="' . route('admin.accounts.show', $account->id) . '" class="font-medium">' . e($account->name) . '</a>')
            ->addColumn('email', fn ($account) => $account->user?->email ?? '-')
            ->addColumn('status_html', fn ($account) => view('accountmodule::Admin.partials.row-status', compact('account'))->render())
            ->editColumn('created_at', fn ($account) => $account->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($account) => view('accountmodule::Admin.partials.row-actions', compact('account'))->render())
            ->rawColumns(['name_html', 'status_html', 'actions'])
            ->only(['name_html', 'email', 'phone', 'status_html', 'created_at', 'actions'])
            ->toJson();
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
