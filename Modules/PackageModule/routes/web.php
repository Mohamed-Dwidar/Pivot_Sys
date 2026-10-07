<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\EmployeeModule\app\Models\Employee;
use Modules\PackageModule\app\Http\Controllers\Account\PackageAccountModuleController;
use Modules\PackageModule\app\Http\Controllers\Employee\PackageEmployeeModuleController;

/**
 *  Packages: managed by the account and by its employees (same actions, see Concerns\PackageActions)
 */
$packageRoutes = function ($controller) {
    Route::get('/', [$controller, 'index'])->name('index');
    Route::get('data', [$controller, 'data'])->name('data');
    Route::get('create', [$controller, 'create'])->name('create');
    Route::post('/', [$controller, 'store'])->name('store');
    Route::get('{id}', [$controller, 'show'])->name('show');
    Route::get('{id}/edit', [$controller, 'edit'])->name('edit');
    Route::put('{id}', [$controller, 'update'])->name('update');
    Route::patch('{id}/active', [$controller, 'toggleActive'])->name('active');
    Route::delete('{id}', [$controller, 'destroy'])->name('destroy');
};

Route::prefix('account/packages')->name('account.packages.')->middleware(['auth', 'user.active:' . Account::class])
    ->group(fn () => $packageRoutes(PackageAccountModuleController::class));

Route::prefix('employee/packages')->name('employee.packages.')->middleware(['auth', 'user.active:' . Employee::class])
    ->group(fn () => $packageRoutes(PackageEmployeeModuleController::class));
