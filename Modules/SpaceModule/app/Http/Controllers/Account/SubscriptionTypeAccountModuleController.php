<?php

namespace Modules\SpaceModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Http\Requests\SubscriptionTypeRequest;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Yajra\DataTables\Facades\DataTables;

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

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        return view('spacemodule::Account.SubscriptionType.index');
    }

    // DataTables server side
    public function data(Request $request)
    {
        return DataTables::eloquent($this->subscriptionTypeService->listQuery($this->accountId()))
            ->filter(function ($query) use ($request) {
                $query->filter(['search' => trim((string) $request->input('search.value'))]);
            })
            ->addColumn('name_html', fn ($subscriptionType) => '<a href="' . route('account.subscription-types.show', $subscriptionType->id) . '" class="font-medium">' . e($subscriptionType->name) . '</a>')
            ->addColumn('options', fn ($subscriptionType) => view('spacemodule::Account.SubscriptionType.partials.options', compact('subscriptionType'))->render())
            ->addColumn('actions', fn ($subscriptionType) => view('spacemodule::Account.SubscriptionType.partials.row-actions', compact('subscriptionType'))->render())
            ->rawColumns(['name_html', 'options', 'actions'])
            ->only(['name_html', 'options', 'spaces_count', 'actions'])
            ->toJson();
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
