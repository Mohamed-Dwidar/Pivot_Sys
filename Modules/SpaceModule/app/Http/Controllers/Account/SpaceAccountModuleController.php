<?php

namespace Modules\SpaceModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Http\Requests\SpaceRequest;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Modules\UnitModule\app\Services\UnitService;

// logged in account: manage his spaces
class SpaceAccountModuleController extends Controller
{
    private $spaceService;
    private $subscriptionTypeService;
    private $unitService;

    public function __construct(SpaceService $spaceService, SubscriptionTypeService $subscriptionTypeService, UnitService $unitService)
    {
        $this->spaceService = $spaceService;
        $this->subscriptionTypeService = $subscriptionTypeService;
        $this->unitService = $unitService;
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    public function index(Request $request)
    {
        $filters = $request->only('search', 'is_active', 'subscription_type_id');
        $spaces = $this->spaceService->paginate($this->accountId(), $filters);
        $subscriptionTypes = $this->subscriptionTypeService->options($this->accountId());

        return view('spacemodule::Account.Space.index', compact('spaces', 'filters', 'subscriptionTypes'));
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
        $units = $this->unitService->listForSpace($space);
        return view('spacemodule::Account.Space.show', compact('space', 'units'));
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
