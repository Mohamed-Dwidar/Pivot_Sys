<?php

namespace Modules\PackageModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Modules\MemberModule\app\Services\MemberService;
use Modules\PackageModule\app\Http\Requests\PackageRequest;
use Modules\PackageModule\app\Services\PackageService;
use Yajra\DataTables\Facades\DataTables;

/**
 * Packages actions shared by the account and the employee controllers (both manage the account's packages).
 * The controller gives area(): "account" | "employee" (route names {area}.packages.*), the views read it from the route.
 * show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
 */
trait PackageActions
{
    private PackageService $packageService;
    private MemberService $memberService;

    abstract protected function area(): string;

    public function __construct(PackageService $packageService, MemberService $memberService)
    {
        $this->packageService = $packageService;
        $this->memberService = $memberService;
    }

    // the account itself, or the account of the logged in employee
    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    private function route($name, $parameters = [])
    {
        return route($this->area() . '.packages.' . $name, $parameters);
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        $members = $this->memberService->options($this->accountId());
        return view('packagemodule::Package.index', compact('members'));
    }

    // DataTables server side: search + member_id / is_active filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->packageService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the packages
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))] + Arr::only($filters, ['member_id', 'is_active'])))
            ->setRowId(fn ($package) => 'row-' . $package->id)
            ->addColumn('name_html', fn ($package) => '<a href="' . $this->route('show', $package->id) . '" data-modal class="font-medium">' . e($package->name) . '</a>')
            ->addColumn('member', fn ($package) => e($package->member?->name ?? '-'))
            ->addColumn('period', fn ($package) => $package->period)
            ->editColumn('after_discount', fn ($package) => number_format($package->after_discount, 2))
            ->editColumn('total_amount', fn ($package) => number_format($package->total_amount, 2))
            ->editColumn('remaining', fn ($package) => '<span class="' . ($package->remaining < 0 ? 'text-danger' : '') . '">' . number_format($package->remaining, 2) . '</span>')
            ->addColumn('status', fn ($package) => view('layoutmodule::partials.row-toggle', [
                'url' => $this->route('active', $package->id), 'row' => $package->id, 'name' => 'is_active', 'checked' => $package->is_active,
            ])->render())
            ->addColumn('actions', fn ($package) => view('packagemodule::Package.partials.row-actions', compact('package'))->render())
            ->rawColumns(['name_html', 'remaining', 'status', 'actions'])
            ->only(['DT_RowId', 'name_html', 'member', 'period', 'after_discount', 'total_amount', 'remaining', 'reservations_count', 'status', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        $members = $this->memberService->options($this->accountId());
        return view('packagemodule::Package.create', compact('members'));
    }

    public function store(PackageRequest $request)
    {
        $package = $this->packageService->create($this->accountId(), $request->validated(), Auth::user());

        return response()->json([
            'message' => 'The package has been added successfully.',
            'reload' => true,
            'redirect' => $this->route('show', $package->id),
        ]);
    }

    public function show($id)
    {
        $package = $this->packageService->findOne($this->accountId(), $id)
            ->load(['reservations' => fn ($query) => $query->with('unit', 'space', 'status', 'plan')->latest('start_at')]);
        return view('packagemodule::Package.show', compact('package'));
    }

    public function edit($id)
    {
        $package = $this->packageService->findOne($this->accountId(), $id);
        $members = $this->memberService->options($this->accountId());
        return view('packagemodule::Package.edit', compact('package', 'members'));
    }

    public function update(PackageRequest $request, $id)
    {
        $this->packageService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The package has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => $this->route('show', $id),
        ]);
    }

    // the active switch in the list (ajax)
    public function toggleActive(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);
        $package = $this->packageService->setActive($this->accountId(), $id, $request->boolean('is_active'));

        return response()->json([
            'message' => $package->is_active ? 'The package has been activated.' : 'The package has been deactivated.',
            'row' => $this->row($request, $id),
            'redirect' => $this->route('show', $id),
        ]);
    }

    public function destroy($id)
    {
        if (!$this->packageService->deleteOne($this->accountId(), $id)) {
            return response()->json(['message' => 'This package has reservations, it can not be deleted. You can deactivate it.'], 422);
        }

        return response()->json([
            'message' => 'The package has been deleted successfully.',
            'row' => null,
            'redirect' => $this->route('index'),
        ]);
    }
}
