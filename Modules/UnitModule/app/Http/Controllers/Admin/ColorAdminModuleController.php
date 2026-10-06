<?php

namespace Modules\UnitModule\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\UnitModule\app\Http\Requests\ColorRequest;
use Modules\UnitModule\app\Services\ColorService;

// admin: colors list (shared by all accounts' units)
class ColorAdminModuleController extends Controller
{
    private $colorService;

    public function __construct(ColorService $colorService)
    {
        $this->colorService = $colorService;
    }

    public function index(Request $request)
    {
        $filters = $request->only('search');
        $colors = $this->colorService->paginate($filters);

        return view('unitmodule::Admin.Color.index', compact('colors', 'filters'));
    }

    public function create()
    {
        return view('unitmodule::Admin.Color.create');
    }

    public function store(ColorRequest $request)
    {
        $this->colorService->create($request->validated());

        return redirect()->route('admin.colors.index')->with('success', 'The color has been added successfully.');
    }

    public function edit($id)
    {
        $color = $this->colorService->findOne($id);
        return view('unitmodule::Admin.Color.edit', compact('color'));
    }

    public function update(ColorRequest $request, $id)
    {
        $this->colorService->update($id, $request->validated());

        return redirect()->route('admin.colors.index')->with('success', 'The color has been updated successfully.');
    }

    public function destroy($id)
    {
        if (!$this->colorService->deleteOne($id)) {
            return back()->withErrors(['color' => 'This color is used by units, change their color first.']);
        }

        return redirect()->route('admin.colors.index')->with('success', 'The color has been deleted successfully.');
    }
}
