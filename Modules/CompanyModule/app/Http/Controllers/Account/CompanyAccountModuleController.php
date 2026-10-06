<?php

namespace Modules\CompanyModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CompanyModule\app\Http\Requests\CompanyRequest;
use Modules\CompanyModule\app\Services\CompanyService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his companies list
// show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class CompanyAccountModuleController extends Controller
{
    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        return view('companymodule::Account.index');
    }

    // DataTables server side
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')])->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->companyService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the companies
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]))
            ->setRowId(fn ($company) => 'row-' . $company->id)
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('name_html', fn ($company) => '<a href="' . route('account.companies.show', $company->id) . '" data-modal class="font-medium">' . e($company->name) . '</a>')
            ->editColumn('created_at', fn ($company) => $company->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($company) => view('companymodule::Account.partials.row-actions', compact('company'))->render())
            ->rawColumns(['name_html', 'actions'])
            ->only(['DT_RowId', 'name_html', 'created_at', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('companymodule::Account.create');
    }

    public function store(CompanyRequest $request)
    {
        $company = $this->companyService->create($this->accountId(), $request->validated());

        return response()->json([
            'message' => 'The company has been added successfully.',
            'reload' => true,
            'redirect' => route('account.companies.show', $company->id),
        ]);
    }

    public function show($id)
    {
        $company = $this->companyService->findOne($this->accountId(), $id);
        return view('companymodule::Account.show', compact('company'));
    }

    public function edit($id)
    {
        $company = $this->companyService->findOne($this->accountId(), $id);
        return view('companymodule::Account.edit', compact('company'));
    }

    public function update(CompanyRequest $request, $id)
    {
        $this->companyService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The company has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.companies.show', $id),
        ]);
    }

    public function destroy($id)
    {
        $this->companyService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The company has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.companies.index'),
        ]);
    }
}
