<?php

namespace Modules\JobModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\JobModule\app\Http\Requests\JobRequest;
use Modules\JobModule\app\Services\JobService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his jobs list
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
        return DataTables::eloquent($this->jobService->listQuery($this->accountId()))
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->filter(['search' => trim((string) $request->input('search.value'))]);
            })
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('name_html', fn ($job) => '<a href="' . route('account.jobs.show', $job->id) . '" class="font-medium">' . e($job->name) . '</a>')
            ->editColumn('created_at', fn ($job) => $job->created_at->format('Y-m-d'))
            ->addColumn('actions', fn ($job) => view('jobmodule::Account.partials.row-actions', compact('job'))->render())
            ->rawColumns(['name_html', 'actions'])
            ->only(['DT_RowIndex', 'name_html', 'created_at', 'actions'])
            ->toJson();
    }

    public function create()
    {
        return view('jobmodule::Account.create');
    }

    public function store(JobRequest $request)
    {
        $job = $this->jobService->create($this->accountId(), $request->validated());

        return redirect()->route('account.jobs.show', $job->id)->with('success', 'The job has been added successfully.');
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

        return redirect()->route('account.jobs.show', $id)->with('success', 'The job has been updated successfully.');
    }

    public function destroy($id)
    {
        $this->jobService->deleteOne($this->accountId(), $id);

        return redirect()->route('account.jobs.index')->with('success', 'The job has been deleted successfully.');
    }
}
