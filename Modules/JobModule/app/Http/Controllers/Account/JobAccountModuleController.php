<?php

namespace Modules\JobModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\JobModule\app\Http\Requests\JobRequest;
use Modules\JobModule\app\Services\JobService;

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

    public function index(Request $request)
    {
        $filters = $request->only('search');
        $jobs = $this->jobService->paginate($this->accountId(), $filters);

        return view('jobmodule::Account.index', compact('jobs', 'filters'));
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
