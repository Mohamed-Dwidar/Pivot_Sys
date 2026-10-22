<?php

namespace Modules\PaymentModule\app\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\PaymentModule\app\Http\Requests\ExtraRequest;
use Modules\PaymentModule\app\Models\Extra;
use Modules\PaymentModule\app\Services\ExtraService;
use Modules\PaymentModule\app\Services\IncomeCategoryService;

/**
 * Add an extra to a package (from the package view, popup), shared by the account and employees (2026-10-21).
 * Employees: the type "extra" only (a sale, positive unit price — ExtraRequest); adjustments, edit and delete = the account.
 * After saving, the popup shows the package view again (json "modal"). The controller gives area(): "account" | "employee".
 */
trait ExtraActions
{
    private ExtraService $extraService;
    private IncomeCategoryService $categoryService;

    abstract protected function area(): string;

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
        $url = route($this->area() . '.packages.show', $extra->extrable_id);
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
}
