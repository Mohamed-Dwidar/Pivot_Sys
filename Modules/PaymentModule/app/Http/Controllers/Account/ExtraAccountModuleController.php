<?php

namespace Modules\PaymentModule\app\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Requests\ExtraRequest;
use Modules\PaymentModule\app\Models\Extra;
use Modules\PaymentModule\app\Services\ExtraService;
use Modules\PaymentModule\app\Services\IncomeCategoryService;

// logged in account: the extras / adjustments of its packages (add / edit / delete in the popup, from the package view);
// after saving, the popup shows the package view again (json "modal")
class ExtraAccountModuleController extends Controller
{
    private $extraService;
    private $categoryService;

    public function __construct(ExtraService $extraService, IncomeCategoryService $categoryService)
    {
        $this->extraService = $extraService;
        $this->categoryService = $categoryService;
    }

    private function accountId()
    {
        return Auth::user()->userable->ownerAccountId();
    }

    private function answer(string $message, Extra $extra)
    {
        $url = route('account.packages.show', $extra->extrable_id);
        return response()->json(['message' => $message, 'reload' => true, 'modal' => $url, 'redirect' => $url]);
    }

    // ?package_id=X
    public function create(Request $request)
    {
        $package = $this->extraService->findExtrable($this->accountId(), 'package', (int) $request->input('package_id'));
        return view('paymentmodule::Extra.create', [
            'package' => $package,
            'extra' => new Extra(['type' => 'extra', 'quantity' => 1, 'date' => today()]),
            'categories' => $this->categoryService->options($this->accountId()),
        ]);
    }

    public function store(ExtraRequest $request)
    {
        $package = $this->extraService->findExtrable($this->accountId(), 'package', (int) $request->input('package_id'));
        $extra = $this->extraService->create($this->accountId(), $package, $request->validated(), Auth::user());

        return $this->answer('The ' . strtolower($extra->type_label) . ' has been added to the package.', $extra);
    }

    public function edit($id)
    {
        $extra = $this->extraService->findOne($this->accountId(), $id);
        return view('paymentmodule::Extra.edit', [
            'package' => $extra->extrable,
            'extra' => $extra,
            'categories' => $this->categoryService->options($this->accountId(), $extra->income_category_id),
        ]);
    }

    public function update(ExtraRequest $request, $id)
    {
        $extra = $this->extraService->update($this->accountId(), $id, $request->validated());
        return $this->answer('The ' . strtolower($extra->type_label) . ' has been updated.', $extra);
    }

    public function destroy($id)
    {
        $extra = $this->extraService->deleteOne($this->accountId(), $id);
        return $this->answer('The ' . strtolower($extra->type_label) . ' has been deleted.', $extra);
    }
}
