<?php

namespace Modules\CompanyModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CompanyModule\app\Http\Requests\CompanyRequest;
use Modules\CompanyModule\app\Services\CompanyService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his companies list
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
        return DataTables::eloquent($this->companyService->listQuery($this->accountId()))
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->filter(['search' => trim((string) $request->input('search.value'))]);
            })
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('name_html', fn ($company) => '<a href="' . route('account.companies.show', $company->id) . '" class="font-medium">' . e($company->name) . '</a>')
            ->editColumn('created_at', fn ($company) => $company->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($company) => view('companymodule::Account.partials.row-actions', compact('company'))->render())
            ->rawColumns(['name_html', 'actions'])
            ->only(['DT_RowIndex', 'name_html', 'created_at', 'actions'])
            ->toJson();
    }

    public function create()
    {
        return view('companymodule::Account.create');
    }

    public function store(CompanyRequest $request)
    {
        $company = $this->companyService->create($this->accountId(), $request->validated());

        return redirect()->route('account.companies.show', $company->id)->with('success', 'The company has been added successfully.');
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

        return redirect()->route('account.companies.show', $id)->with('success', 'The company has been updated successfully.');
    }

    public function destroy($id)
    {
        $this->companyService->deleteOne($this->accountId(), $id);

        return redirect()->route('account.companies.index')->with('success', 'The company has been deleted successfully.');
    }
}
