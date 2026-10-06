<?php

namespace Modules\SpaceModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PlanModule\app\Services\PlanService;
use Modules\SpaceModule\app\Http\Requests\SpaceRequest;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his spaces
// show (details) / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class SpaceAccountModuleController extends Controller
{
    private $spaceService;
    private $subscriptionTypeService;
    private $planService;

    public function __construct(SpaceService $spaceService, SubscriptionTypeService $subscriptionTypeService, PlanService $planService)
    {
        $this->spaceService = $spaceService;
        $this->subscriptionTypeService = $subscriptionTypeService;
        $this->planService = $planService;
    }

    // subscription types + plans checkboxes of the form
    private function formOptions(): array
    {
        return [
            'subscriptionTypes' => $this->subscriptionTypeService->options($this->accountId()),
            'plans' => $this->planService->options($this->accountId()),
        ];
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        $subscriptionTypes = $this->subscriptionTypeService->options($this->accountId());
        return view('spacemodule::Account.Space.index', compact('subscriptionTypes'));
    }

    // DataTables server side: search + subscription_type_id / is_active filters
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->spaceService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            // inside filter() so "of N total" counts all the spaces
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))] + Arr::only($filters, ['subscription_type_id', 'is_active'])))
            ->setRowId(fn ($space) => 'row-' . $space->id)
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('image', fn ($space) => view('spacemodule::Account.Space.partials.row-image', compact('space'))->render())
            // the space page has its units
            ->addColumn('name_html', fn ($space) => '<a href="' . route('account.spaces.show', $space->id) . '" class="font-medium">' . e($space->name) . '</a>')
            ->addColumn('subscription_types', fn ($space) => $space->subscriptionTypes->pluck('name')->join(', ') ?: '-')
            ->addColumn('images_count', fn ($space) => $space->images->count())
            ->addColumn('status', fn ($space) => view('layoutmodule::partials.row-toggle', [
                'url' => route('account.spaces.active', $space->id), 'row' => $space->id, 'name' => 'is_active', 'checked' => $space->is_active,
            ])->render())
            ->addColumn('actions', fn ($space) => view('spacemodule::Account.Space.partials.row-actions', compact('space'))->render())
            ->rawColumns(['image', 'name_html', 'status', 'actions'])
            ->only(['DT_RowId', 'image', 'name_html', 'subscription_types', 'units_count', 'images_count', 'status', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('spacemodule::Account.Space.create', $this->formOptions());
    }

    public function store(SpaceRequest $request)
    {
        $space = $this->spaceService->create($this->accountId(), $request->validated());

        return response()->json([
            'message' => 'The space has been added successfully.',
            'reload' => true,
            'redirect' => route('account.spaces.show', $space->id),
        ]);
    }

    public function show($id)
    {
        $space = $this->spaceService->findOne($this->accountId(), $id);
        return view('spacemodule::Account.Space.show', compact('space'));
    }

    public function edit($id)
    {
        $space = $this->spaceService->findOne($this->accountId(), $id);
        return view('spacemodule::Account.Space.edit', compact('space') + $this->formOptions());
    }

    public function update(SpaceRequest $request, $id)
    {
        $this->spaceService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The space has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.spaces.show', $id),
        ]);
    }

    // the active switch in the list (ajax)
    public function toggleActive(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);
        $space = $this->spaceService->setActive($this->accountId(), $id, $request->boolean('is_active'));

        return response()->json([
            'message' => $space->is_active ? 'The space has been activated.' : 'The space has been deactivated.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.spaces.show', $id),
        ]);
    }

    public function destroy($id)
    {
        $this->spaceService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The space has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.spaces.index'),
        ]);
    }
}
