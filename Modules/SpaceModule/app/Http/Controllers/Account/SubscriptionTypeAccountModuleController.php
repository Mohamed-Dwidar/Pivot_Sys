<?php

namespace Modules\SpaceModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Http\Requests\SubscriptionTypeRequest;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;

// logged in account: manage his subscription types
class SubscriptionTypeAccountModuleController extends Controller
{
    private $subscriptionTypeService;

    public function __construct(SubscriptionTypeService $subscriptionTypeService)
    {
        $this->subscriptionTypeService = $subscriptionTypeService;
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    public function index(Request $request)
    {
        $filters = $request->only('search');
        $subscriptionTypes = $this->subscriptionTypeService->paginate($this->accountId(), $filters);

        return view('spacemodule::Account.SubscriptionType.index', compact('subscriptionTypes', 'filters'));
    }

    public function create()
    {
        return view('spacemodule::Account.SubscriptionType.create');
    }

    public function store(SubscriptionTypeRequest $request)
    {
        $subscriptionType = $this->subscriptionTypeService->create($this->accountId(), $request->validated());

        return redirect()->route('account.subscription-types.show', $subscriptionType->id)->with('success', 'The subscription type has been added successfully.');
    }

    public function show($id)
    {
        $subscriptionType = $this->subscriptionTypeService->findOne($this->accountId(), $id);
        return view('spacemodule::Account.SubscriptionType.show', compact('subscriptionType'));
    }

    public function edit($id)
    {
        $subscriptionType = $this->subscriptionTypeService->findOne($this->accountId(), $id);
        return view('spacemodule::Account.SubscriptionType.edit', compact('subscriptionType'));
    }

    public function update(SubscriptionTypeRequest $request, $id)
    {
        $this->subscriptionTypeService->update($this->accountId(), $id, $request->validated());

        return redirect()->route('account.subscription-types.show', $id)->with('success', 'The subscription type has been updated successfully.');
    }

    public function destroy($id)
    {
        $this->subscriptionTypeService->deleteOne($this->accountId(), $id);

        return redirect()->route('account.subscription-types.index')->with('success', 'The subscription type has been deleted successfully.');
    }
}
