<?php

namespace Modules\SpaceModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Http\Requests\SubscriptionTypeRequest;
use Modules\SpaceModule\app\Services\SubscriptionTypeService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: manage his subscription types
// show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
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
        return $this->table(['search' => $request->input('search.value')])->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->subscriptionTypeService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]))
            ->setRowId(fn ($subscriptionType) => 'row-' . $subscriptionType->id)
            ->addColumn('name_html', fn ($subscriptionType) => '<a href="' . route('account.subscription-types.show', $subscriptionType->id) . '" data-modal class="font-medium">' . e($subscriptionType->name) . '</a>')
            ->addColumn('options', fn ($subscriptionType) => view('spacemodule::Account.SubscriptionType.partials.options', compact('subscriptionType'))->render())
            ->addColumn('actions', fn ($subscriptionType) => view('spacemodule::Account.SubscriptionType.partials.row-actions', compact('subscriptionType'))->render())
            ->rawColumns(['name_html', 'options', 'actions'])
            ->only(['DT_RowId', 'name_html', 'options', 'spaces_count', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('spacemodule::Account.SubscriptionType.create');
    }

    public function store(SubscriptionTypeRequest $request)
    {
        $subscriptionType = $this->subscriptionTypeService->create($this->accountId(), $request->validated());

        return response()->json([
            'message' => 'The subscription type has been added successfully.',
            'reload' => true,
            'redirect' => route('account.subscription-types.show', $subscriptionType->id),
        ]);
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

        return response()->json([
            'message' => 'The subscription type has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.subscription-types.show', $id),
        ]);
    }

    public function destroy($id)
    {
        $this->subscriptionTypeService->deleteOne($this->accountId(), $id);

        return response()->json([
            'message' => 'The subscription type has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.subscription-types.index'),
        ]);
    }
}
