<?php

namespace Modules\JobModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\JobModule\app\Http\Requests\JobRequest;
use Modules\JobModule\app\Services\JobService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his jobs list
// show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class JobAccountModuleController extends Controller
{
    private $jobService;

    public function __construct(JobService $jobService)
    {
        $this->jobService = $jobService;
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        return view('jobmodule::Account.index');
    }

    // DataTables server side
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')])->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->jobService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the jobs
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]))
            ->setRowId(fn ($job) => 'row-' . $job->id)
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('name_html', fn ($job) => '<a href="' . route('account.jobs.show', $job->id) . '" data-modal class="font-medium">' . e($job->name) . '</a>')
            ->editColumn('created_at', fn ($job) => $job->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($job) => view('jobmodule::Account.partials.row-actions', compact('job'))->render())
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
        return view('jobmodule::Account.create');
    }

    public function store(JobRequest $request)
    {
        $job = $this->jobService->create($this->accountId(), $request->validated());

        return response()->json([
            'message' => 'The job has been added successfully.',
            'reload' => true,
            'redirect' => route('account.jobs.show', $job->id),
        ]);
    }

    public function show($id)
    {
        $job = $this->jobService->findOne($this->accountId(), $id);
        return view('jobmodule::Account.show', compact('job'));
    }

    public function edit($id)
    {
        $job = $this->jobService->findOne($this->accountId(), $id);
        return view('jobmodule::Account.edit', compact('job'));
    }

    public function update(JobRequest $request, $id)
    {
        $this->jobService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The job has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.jobs.show', $id),
        ]);
    }

    public function destroy($id)
    {
        $this->jobService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The job has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.jobs.index'),
        ]);
    }
}
