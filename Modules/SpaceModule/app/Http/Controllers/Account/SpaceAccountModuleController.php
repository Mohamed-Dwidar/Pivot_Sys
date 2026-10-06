<?php

namespace Modules\SpaceModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Http\Requests\SpaceRequest;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his spaces
class SpaceAccountModuleController extends Controller
{
    private $spaceService;
    private $subscriptionTypeService;

    public function __construct(SpaceService $spaceService, SubscriptionTypeService $subscriptionTypeService)
    {
        $this->spaceService = $spaceService;
        $this->subscriptionTypeService = $subscriptionTypeService;
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
        return DataTables::eloquent($this->spaceService->listQuery($this->accountId()))
            // inside filter() so "of N total" counts all the spaces
            ->filter(function ($query) use ($request) {
                $query->filter(['search' => trim((string) $request->input('search.value'))] + $request->only('subscription_type_id', 'is_active'));
            })
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('image', fn ($space) => view('spacemodule::Account.Space.partials.row-image', compact('space'))->render())
            ->addColumn('name_html', fn ($space) => '<a href="' . route('account.spaces.show', $space->id) . '" class="font-medium">' . e($space->name) . '</a>')
            ->addColumn('subscription_types', fn ($space) => $space->subscriptionTypes->pluck('name')->join(', ') ?: '-')
            ->addColumn('images_count', fn ($space) => $space->images->count())
            ->addColumn('status', fn ($space) => view('spacemodule::Account.Space.partials.status-badge', compact('space'))->render())
            ->addColumn('actions', fn ($space) => view('spacemodule::Account.Space.partials.row-actions', compact('space'))->render())
            ->rawColumns(['image', 'name_html', 'status', 'actions'])
            ->only(['image', 'name_html', 'subscription_types', 'units_count', 'images_count', 'status', 'actions'])
            ->toJson();
    }

    public function create()
    {
        $subscriptionTypes = $this->subscriptionTypeService->options($this->accountId());
        return view('spacemodule::Account.Space.create', compact('subscriptionTypes'));
    }

    public function store(SpaceRequest $request)
    {
        $space = $this->spaceService->create($this->accountId(), $request->validated());

        return redirect()->route('account.spaces.show', $space->id)->with('success', 'The space has been added successfully.');
    }

    public function show($id)
    {
        $space = $this->spaceService->findOne($this->accountId(), $id);
        return view('spacemodule::Account.Space.show', compact('space'));
    }

    public function edit($id)
    {
        $space = $this->spaceService->findOne($this->accountId(), $id);
        $subscriptionTypes = $this->subscriptionTypeService->options($this->accountId());
        return view('spacemodule::Account.Space.edit', compact('space', 'subscriptionTypes'));
    }

    public function update(SpaceRequest $request, $id)
    {
        $this->spaceService->update($this->accountId(), $id, $request->validated());

        return redirect()->route('account.spaces.show', $id)->with('success', 'The space has been updated successfully.');
    }

    public function destroy($id)
    {
        $this->spaceService->deleteOne($this->accountId(), $id);

        return redirect()->route('account.spaces.index')->with('success', 'The space has been deleted successfully.');
    }
}
