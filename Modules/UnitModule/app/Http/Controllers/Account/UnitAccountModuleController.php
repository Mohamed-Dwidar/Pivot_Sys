<?php

namespace Modules\UnitModule\app\Http\Controllers\Account;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SpaceModule\app\Services\SpaceService;
use Modules\UnitModule\app\Http\Requests\UnitRequest;
use Modules\UnitModule\app\Services\ColorService;
use Modules\UnitModule\app\Services\UnitService;

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
