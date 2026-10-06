<?php

namespace Modules\MemberModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CompanyModule\app\Services\CompanyService;
use Modules\JobModule\app\Services\JobService;
use Modules\MemberModule\app\Http\Requests\MemberRequest;
use Modules\MemberModule\app\Services\MemberService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his members
// show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
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

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        return view('membermodule::Account.index', $this->formOptions());
    }

    // DataTables server side: search (search.value) + company_id / job_id filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->memberService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the members
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))] + Arr::only($filters, ['company_id', 'job_id'])))
            ->setRowId(fn ($member) => 'row-' . $member->id)
            ->addColumn('name_html', fn ($member) => view('membermodule::Account.partials.row-name', compact('member'))->render())
            ->addColumn('company', fn ($member) => $member->company?->name ?? '-')
            ->addColumn('job', fn ($member) => $member->job?->name ?? '-')
            ->editColumn('created_at', fn ($member) => $member->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($member) => view('membermodule::Account.partials.row-actions', compact('member'))->render())
            ->rawColumns(['name_html', 'actions'])
            // send only the table columns (not all the member fields)
            ->only(['DT_RowId', 'name_html', 'phone', 'company', 'job', 'created_at', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('membermodule::Account.create', $this->formOptions());
    }

    public function store(MemberRequest $request)
    {
        $member = $this->memberService->create($this->accountId(), $request->validated(), Auth::user());

        return response()->json([
            'message' => 'The member has been added successfully.',
            'reload' => true,
            'redirect' => route('account.members.show', $member->id),
        ]);
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

        return response()->json([
            'message' => 'The member has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.members.show', $id),
        ]);
    }

    public function destroy($id)
    {
        $this->memberService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The member has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.members.index'),
        ]);
    }
}
