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
            'colors' => $this->colorService->options(),
            'colorValues' => $this->colorService->values(),
        ];
    }

    // DataTables server side: the units table on the space page
    public function data(Request $request, $spaceId)
    {
        $space = $this->space($spaceId);

        return DataTables::eloquent($this->unitService->listQuery($space))
            ->filter(function ($query) use ($request) {
                $search = trim((string) $request->input('search.value'));
                if ($search !== '') {
                    $query->where(fn ($query) => $query->where('name_ar', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%"));
                }
            })
            ->orderColumn('name', fn ($query, $order) => $query->orderByLocalized('name', $order))
            ->addColumn('image', fn ($unit) => view('unitmodule::Account.Unit.partials.row-image', compact('unit'))->render())
            ->addColumn('name_html', fn ($unit) => view('unitmodule::Account.Unit.partials.row-name', ['space' => $space, 'unit' => $unit])->render())
            ->addColumn('subscription_type', fn ($unit) => $unit->subscriptionType?->name ?? '-')
            ->editColumn('capacity', fn ($unit) => $unit->capacity ?: '-')
            ->addColumn('status', fn ($unit) => '<span class="badge ' . ($unit->is_active ? 'badge-active">Active' : 'badge-inactive">Inactive') . '</span>')
            ->addColumn('actions', fn ($unit) => view('unitmodule::Account.Unit.partials.row-actions', ['space' => $space, 'unit' => $unit])->render())
            ->rawColumns(['image', 'name_html', 'status', 'actions'])
            ->only(['image', 'name_html', 'subscription_type', 'capacity', 'concurrent_usage', 'status', 'actions'])
            ->toJson();
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

        return redirect()->route('account.spaces.units.show', [$space->id, $unit->id])->with('success', 'The unit has been added successfully.');
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

        return redirect()->route('account.spaces.units.show', [$space->id, $id])->with('success', 'The unit has been updated successfully.');
    }

    public function destroy($spaceId, $id)
    {
        $space = $this->space($spaceId);
        $this->unitService->deleteOne($space, $id);

        return redirect()->route('account.spaces.show', $space->id)->with('success', 'The unit has been deleted successfully.');
    }
}
