<?php

namespace Modules\MemberModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CompanyModule\app\Services\CompanyService;
use Modules\JobModule\app\Services\JobService;
use Modules\MemberModule\app\Http\Requests\MemberRequest;
use Modules\MemberModule\app\Services\MemberService;

// logged in account: manage his members
class MemberAccountModuleController extends Controller
{
    private $memberService;
    private $companyService;
    private $jobService;

    public function __construct(MemberService $memberService, CompanyService $companyService, JobService $jobService)
    {
        $this->memberService = $memberService;
        $this->companyService = $companyService;
        $this->jobService = $jobService;
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    // companies / jobs dropdowns
    private function formOptions(): array
    {
        return [
            'companies' => $this->companyService->options($this->accountId()),
            'jobs' => $this->jobService->options($this->accountId()),
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'company_id', 'job_id');
        $members = $this->memberService->paginate($this->accountId(), $filters);

        return view('membermodule::Account.index', compact('members', 'filters') + $this->formOptions());
    }

    public function create()
    {
        return view('membermodule::Account.create', $this->formOptions());
    }

    public function store(MemberRequest $request)
    {
        $member = $this->memberService->create($this->accountId(), $request->validated(), Auth::user());

        return redirect()->route('account.members.show', $member->id)->with('success', 'The member has been added successfully.');
    }

    public function show($id)
    {
        $member = $this->memberService->findOne($this->accountId(), $id);
        return view('membermodule::Account.show', compact('member'));
    }

    public function edit($id)
    {
        $member = $this->memberService->findOne($this->accountId(), $id);
        return view('membermodule::Account.edit', compact('member') + $this->formOptions());
    }

    public function update(MemberRequest $request, $id)
    {
        $this->memberService->update($this->accountId(), $id, $request->validated());

        return redirect()->route('account.members.show', $id)->with('success', 'The member has been updated successfully.');
    }

    public function destroy($id)
    {
        $this->memberService->deleteOne($this->accountId(), $id);

        return redirect()->route('account.members.index')->with('success', 'The member has been deleted successfully.');
    }
}
