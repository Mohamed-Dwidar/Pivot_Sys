<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\PlanModule\app\Http\Controllers\Account\PlanAccountModuleController;

/**
 *  Logged in account: plans (assigned to spaces / units from their forms)
 */
Route::prefix('account/plans')->name('account.plans.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('/', [PlanAccountModuleController::class, 'index'])->name('index');
    Route::get('data', [PlanAccountModuleController::class, 'data'])->name('data');
    Route::get('create', [PlanAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [PlanAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}', [PlanAccountModuleController::class, 'show'])->name('show');
    Route::get('{id}/edit', [PlanAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [PlanAccountModuleController::class, 'update'])->name('update');
    Route::patch('{id}/active', [PlanAccountModuleController::class, 'toggleActive'])->name('active');
    Route::delete('{id}', [PlanAccountModuleController::class, 'destroy'])->name('destroy');
});
