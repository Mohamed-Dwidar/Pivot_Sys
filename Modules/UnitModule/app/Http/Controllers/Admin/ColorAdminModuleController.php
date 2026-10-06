<?php

namespace Modules\UnitModule\app\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\UnitModule\app\Http\Requests\ColorRequest;
use Modules\UnitModule\app\Services\ColorService;
use Yajra\DataTables\Facades\DataTables;

// admin: colors list (shared by all accounts' units)
// create / edit open in the popup (ajax), store / update / destroy answer JSON (custom.js ajax forms)
class ColorAdminModuleController extends Controller
{
    private $colorService;

    public function __construct(ColorService $colorService)
    {
        $this->colorService = $colorService;
    }

    // the list page, the rows come from data() (DataTables)
    public function index()
    {
        return view('unitmodule::Admin.Color.index');
    }

    // DataTables server side
    public function data(Request $request)
    {
        return $this->table(['search' => $request->input('search.value')])->toJson();
    }

    // the list columns, for data() and for one row after a change (row())
    private function table(array $filters, $id = null)
    {
        $query = $this->colorService->listQuery();
        if ($id) {
            $query->whereKey($id);
        }

        return DataTables::eloquent($query)
            ->filter(fn ($query) => $query->filter(['search' => trim((string) ($filters['search'] ?? ''))]))
            ->setRowId(fn ($color) => 'row-' . $color->id)
            ->addColumn('name_html', fn ($color) => view('unitmodule::Admin.Color.partials.row-name', compact('color'))->render())
            ->addColumn('actions', fn ($color) => view('unitmodule::Admin.Color.partials.row-actions', compact('color'))->render())
            ->rawColumns(['name_html', 'actions'])
            ->only(['DT_RowId', 'name_html', 'value', 'units_count', 'actions']);
    }

    // the updated row with the list filters (list[...]), null when it does not match them any more
    private function row(Request $request, $id)
    {
        return $this->table((array) $request->input('list', []), $id)->toArray()['data'][0] ?? null;
    }

    public function create()
    {
        return view('unitmodule::Admin.Color.create');
    }

    public function store(ColorRequest $request)
    {
        $this->colorService->create($request->validated());

        return response()->json([
            'message' => 'The color has been added successfully.',
            'reload' => true,
            'redirect' => route('admin.colors.index'),
        ]);
    }

    public function edit($id)
    {
        $color = $this->colorService->findOne($id);
        return view('unitmodule::Admin.Color.edit', compact('color'));
    }

    public function update(ColorRequest $request, $id)
    {
        $this->colorService->update($id, $request->validated());

        return response()->json([
            'message' => 'The color has been updated successfully.',
            'row' => $this->row($request, $id),
            'redirect' => route('admin.colors.index'),
        ]);
    }

    public function destroy($id)
    {
        if (!$this->colorService->deleteOne($id)) {
            return response()->json(['message' => 'This color is used by units, change their color first.'], 422);
        }

        return response()->json([
            'message' => 'The color has been deleted successfully.',
            'row' => null,
            'redirect' => route('admin.colors.index'),
        ]);
    }
}
