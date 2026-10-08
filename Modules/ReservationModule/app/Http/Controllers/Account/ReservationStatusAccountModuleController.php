<?php

namespace Modules\ReservationModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Modules\ReservationModule\app\Http\Requests\ReservationStatusRequest;
use Modules\ReservationModule\app\Services\ReservationStatusService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: his reservation statuses (only the account manages them, the reservation form selects one)
// create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class ReservationStatusAccountModuleController extends Controller
{
    private $statusService;

    public function __construct(ReservationStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    private function accountId()
    {
        return Auth::user()->userable_id;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        return view('reservationmodule::Status.index');
    }

    // DataTables server side: search + is_active filter
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')] + $request->all())->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->statusService->listQuery($this->accountId());
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))] + Arr::only($filters, ['is_active'])))
            ->setRowId(fn ($status) => 'row-' . $status->id)
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->orderColumn('sort_order', fn ($query, $order) => $query->orderBy('sort_order', $order)->orderBy('id', $order))
            // the name as its colored badge (+ "not counted")
            ->addColumn('name_html', fn ($status) => '<a href="' . route('account.reservation-statuses.edit', $status->id) . '" data-modal class="' . $status->badge_class . '">' . e($status->name) . '</a>'
                . ($status->is_counted ? '' : ' <span class="ml-1 text-xs text-slate-500">not counted</span>'))
            // the default status (given to the new reservations): switch on another one to change it
            ->addColumn('default', fn ($status) => view('layoutmodule::partials.row-toggle', [
                'url' => route('account.reservation-statuses.default', $status->id), 'row' => $status->id, 'name' => 'is_default', 'checked' => $status->is_default,
                'onLabel' => 'Default', 'offLabel' => 'No',
                'title' => $status->is_default ? 'The default status of the new reservations' : 'Click to make it the default status',
            ])->render())
            ->addColumn('status', fn ($status) => view('layoutmodule::partials.row-toggle', [
                'url' => route('account.reservation-statuses.active', $status->id), 'row' => $status->id, 'name' => 'is_active', 'checked' => $status->is_active,
            ])->render())
            ->addColumn('actions', fn ($status) => view('reservationmodule::Status.partials.row-actions', compact('status'))->render())
            ->rawColumns(['name_html', 'default', 'status', 'actions'])
            ->only(['DT_RowId', 'sort_order', 'name_html', 'reservations_count', 'default', 'status', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('reservationmodule::Status.create');
    }

    public function store(ReservationStatusRequest $request)
    {
        $this->statusService->create($this->accountId(), $request->validated());

        return response()->json([
            'message' => 'The status has been added successfully.',
            'reload' => true,
            'redirect' => route('account.reservation-statuses.index'),
        ]);
    }

    public function edit($id)
    {
        $status = $this->statusService->findOne($this->accountId(), $id);
        return view('reservationmodule::Status.edit', compact('status'));
    }

    public function update(ReservationStatusRequest $request, $id)
    {
        $this->statusService->update($this->accountId(), $id, $request->validated());

        return response()->json([
            'message' => 'The status has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.reservation-statuses.index'),
        ]);
    }

    // the active switch in the list (ajax)
    public function toggleActive(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);
        $status = $this->statusService->setActive($this->accountId(), $id, $request->boolean('is_active'));
        if (!$status) {
            return response()->json(['message' => 'The default status must be active, choose another default status first.'], 422);
        }

        return response()->json([
            'message' => $status->is_active ? 'The status has been activated.' : 'The status has been deactivated.',
            'row' => $this->row($request, $id),
            'redirect' => route('account.reservation-statuses.index'),
        ]);
    }

    // the default switch in the list (ajax): the whole list is reloaded (the previous default is switched off)
    public function toggleDefault(Request $request, $id)
    {
        $request->validate(['is_default' => 'required|boolean']);
        if ($error = $this->statusService->setDefault($this->accountId(), $id, $request->boolean('is_default'))) {
            return response()->json(['message' => $error], 422);
        }

        return response()->json([
            'message' => 'The default status has been changed, the new reservations get it.',
            'reload' => true,
            'redirect' => route('account.reservation-statuses.index'),
        ]);
    }

    public function destroy($id)
    {
        if ($error = $this->statusService->deleteOne($this->accountId(), $id)) {
            return response()->json(['message' => $error], 422);
        }

        return response()->json([
            'message' => 'The status has been deleted successfully.',
            'row' => null,
            'redirect' => route('account.reservation-statuses.index'),
        ]);
    }
}
