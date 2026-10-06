<?php

namespace Modules\UnitModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\UnitModule\app\Http\Requests\UnitRequest;
use Modules\UnitModule\app\Services\ColorService;
use Modules\UnitModule\app\Services\UnitService;
use Yajra\DataTables\Facades\DataTables;

// logged in account: units of one of his spaces (listed on the space page)
// show / create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class UnitAccountModuleController extends Controller
{
    private $unitService;
    private $spaceService;
    private $colorService;

    public function __construct(UnitService $unitService, SpaceService $spaceService, ColorService $colorService)
    {
        $this->unitService = $unitService;
        $this->spaceService = $spaceService;
        $this->colorService = $colorService;
    }

    // 404 when the space belongs to another account
    private function space($spaceId)
    {
        return $this->spaceService->findOne(Auth::user()->userable_id, $spaceId);
    }

    // subscription types of the space + colors dropdowns
    private function formOptions($space): array
    {
        return [
            'subscriptionTypes' => $space->subscriptionTypes->pluck('name', 'id')->all(),
            'plans' => $space->plans->sortBy('name')->pluck('name', 'id')->all(),
            'colors' => $this->colorService->options(),
            'colorValues' => $this->colorService->values(),
        ];
    }

    // DataTables server side: the units table on the space page
    public function data(Request $request, $spaceId)
    {
        return $this->table($this->space($spaceId), ['search' => $request->input('search.value')])->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table($space, array $filters, $id = null)
    {
        $query = $this->unitService->listQuery($space);
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            ->filter(function ($query) use ($filters) {
                $search = trim((string) ($filters['search'] ?? ''));
                if ($search !== '') {
                    $query->where(fn ($query) => $query->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%"));
                }
            })
            ->setRowId(fn ($unit) => 'row-' . $unit->id)
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('image', fn ($unit) => view('unitmodule::Account.Unit.partials.row-image', compact('unit'))->render())
            ->addColumn('name_html', fn ($unit) => view('unitmodule::Account.Unit.partials.row-name', ['space' => $space, 'unit' => $unit])->render())
            ->addColumn('subscription_type', fn ($unit) => $unit->subscriptionType?->name ?? '-')
            ->editColumn('capacity', fn ($unit) => $unit->capacity ?: '-')
            ->addColumn('status', fn ($unit) => view('layoutmodule::partials.row-toggle', [
                'url' => route('account.spaces.units.active', [$space->id, $unit->id]), 'row' => $unit->id, 'name' => 'is_active', 'checked' => $unit->is_active,
            ])->render())
            ->addColumn('actions', fn ($unit) => view('unitmodule::Account.Unit.partials.row-actions', ['space' => $space, 'unit' => $unit])->render())
            ->rawColumns(['image', 'name_html', 'status', 'actions'])
            ->only(['DT_RowId', 'image', 'name_html', 'subscription_type', 'capacity', 'concurrent_usage', 'status', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $space, $id)
    {
        return $this->table($space, (array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create($spaceId)
    {
        $space = $this->space($spaceId);
        return view('unitmodule::Account.Unit.create', compact('space') + $this->formOptions($space));
    }

    public function store(UnitRequest $request, $spaceId)
    {
        $space = $this->space($spaceId);
        $unit = $this->unitService->create($space, $request->validated());

        return response()->json([
            'message' => 'The unit has been added successfully.',
            'reload' => true,
            'counts' => ['units' => $this->unitService->countForSpace($space)],
            'redirect' => route('account.spaces.units.show', [$space->id, $unit->id]),
        ]);
    }

    public function show($spaceId, $id)
    {
        $space = $this->space($spaceId);
        $unit = $this->unitService->findOne($space, $id);
        return view('unitmodule::Account.Unit.show', compact('space', 'unit'));
    }

    public function edit($spaceId, $id)
    {
        $space = $this->space($spaceId);
        $unit = $this->unitService->findOne($space, $id);
        return view('unitmodule::Account.Unit.edit', compact('space', 'unit') + $this->formOptions($space));
    }

    public function update(UnitRequest $request, $spaceId, $id)
    {
        $space = $this->space($spaceId);
        $this->unitService->update($space, $id, $request->validated());

        return response()->json([
            'message' => 'The unit has been updated successfully.',
            'row' => $this->row($request, $space, $id),
            'redirect' => route('account.spaces.units.show', [$space->id, $id]),
        ]);
    }

    // the active switch in the list (ajax)
    public function toggleActive(Request $request, $spaceId, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);
        $space = $this->space($spaceId);
        $unit = $this->unitService->setActive($space, $id, $request->boolean('is_active'));

        return response()->json([
            'message' => $unit->is_active ? 'The unit has been activated.' : 'The unit has been deactivated.',
            'row' => $this->row($request, $space, $id),
            'redirect' => route('account.spaces.units.show', [$space->id, $id]),
        ]);
    }

    public function destroy($spaceId, $id)
    {
        $space = $this->space($spaceId);
        $this->unitService->deleteOne($space, $id);

        return response()->json([
            'message' => 'The unit has been deleted successfully.',
            'row' => null,
            'counts' => ['units' => $this->unitService->countForSpace($space)],
            'redirect' => route('account.spaces.show', $space->id),
        ]);
    }
}
