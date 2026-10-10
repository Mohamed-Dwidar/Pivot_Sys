<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\PaymentModule\app\Http\Controllers\Account\IncomeCategoryAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Account\PaymentMethodAccountModuleController;

/**
 *  Payment settings (payment methods, income categories): only the account manages them
 */
$settingRoutes = function ($controller) {
    Route::get('/', [$controller, 'index'])->name('index');
    Route::get('data', [$controller, 'data'])->name('data');
    Route::get('create', [$controller, 'create'])->name('create');
    Route::post('/', [$controller, 'store'])->name('store');
    Route::get('{id}/edit', [$controller, 'edit'])->name('edit');
    Route::put('{id}', [$controller, 'update'])->name('update');
    Route::patch('{id}/active', [$controller, 'toggleActive'])->name('active');
    Route::delete('{id}', [$controller, 'destroy'])->name('destroy');
};

Route::prefix('account/payment-methods')->name('account.payment-methods.')->middleware(['auth', 'user.active:' . Account::class])
    ->group(fn () => $settingRoutes(PaymentMethodAccountModuleController::class));

Route::prefix('account/income-categories')->name('account.income-categories.')->middleware(['auth', 'user.active:' . Account::class])
    ->group(fn () => $settingRoutes(IncomeCategoryAccountModuleController::class));
