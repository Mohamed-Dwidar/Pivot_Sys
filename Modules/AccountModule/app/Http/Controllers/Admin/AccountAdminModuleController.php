<?php

namespace Modules\AccountModule\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\AccountModule\app\Http\Requests\AccountRequest;
use Modules\AccountModule\app\Models\Account;
use Modules\AccountModule\app\Services\AccountService;
use Yajra\DataTables\Facades\DataTables;

// show / create / edit open in the popup (ajax), the forms answer JSON (custom.js ajax forms)
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
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->accountService->listQuery();
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the accounts
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))] + Arr::only($filters, ['status'])))
            ->setRowId(fn ($account) => 'row-' . $account->id)
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('name_html', fn ($account) => $account->trashed()
                ? '<span class="font-medium">' . e($account->name) . '</span>'
                : '<a href="' . route('admin.accounts.show', $account->id) . '" data-modal class="font-medium">' . e($account->name) . '</a>')
            ->addColumn('email', fn ($account) => $account->user?->email ?? '-')
            ->addColumn('status_html', fn ($account) => view('accountmodule::Admin.partials.row-status', compact('account'))->render())
            ->editColumn('created_at', fn ($account) => $account->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($account) => view('accountmodule::Admin.partials.row-actions', compact('account'))->render())
            ->rawColumns(['name_html', 'status_html', 'actions'])
            ->only(['DT_RowId', 'name_html', 'email', 'phone', 'status_html', 'created_at', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more (e.g. approved in the Pending tab)
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    // the numbers of the status tabs + the sidebar "Pending Requests" badge ([data-counter="accounts-..."])
    private function counts(): array
    {
        $counts = $this->accountService->countByStatus();

        return collect($counts)->mapWithKeys(fn ($count, $status) => ['accounts-' . $status => $count])->all() + [
            'accounts-all' => array_sum($counts),
            'accounts-deleted' => $this->accountService->countDeleted(),
        ];
    }

    public function create()
    {
        return view('accountmodule::Admin.create');
    }

    public function store(AccountRequest $request)
    {
        $account = $this->accountService->create($request->validated());

        return response()->json([
            'message' => 'The account has been created successfully.',
            'reload' => true,
            'counts' => $this->counts(),
            'redirect' => route('admin.accounts.show', $account->id),
        ]);
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

        return response()->json([
            'message' => 'The account has been updated successfully.',
            'row' => $this->row($request, $id),
            'counts' => $this->counts(),
            'redirect' => route('admin.accounts.show', $id),
        ]);
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

        return response()->json([
            'message' => $messages[$request->status],
            'row' => $this->row($request, $id),
            'counts' => $this->counts(),
            'redirect' => route('admin.accounts.show', $id),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->accountService->deleteOne($id);

        return response()->json([
            'message' => 'The account has been deleted successfully.',
            'row' => $this->row($request, $id),
            'counts' => $this->counts(),
            'redirect' => route('admin.accounts.index'),
        ]);
    }

    public function restore(Request $request, $id)
    {
        $this->accountService->restore($id);

        return response()->json([
            'message' => 'The account has been restored successfully.',
            'row' => $this->row($request, $id),
            'counts' => $this->counts(),
            'redirect' => route('admin.accounts.show', $id),
        ]);
    }
}
