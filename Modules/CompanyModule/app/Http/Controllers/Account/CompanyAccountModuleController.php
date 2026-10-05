<?php

namespace Modules\CompanyModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CompanyModule\app\Http\Requests\CompanyRequest;
use Modules\CompanyModule\app\Services\CompanyService;

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

    public function index(Request $request)
    {
        $filters = $request->only('search');
        $companies = $this->companyService->paginate($this->accountId(), $filters);

        return view('companymodule::Account.index', compact('companies', 'filters'));
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
