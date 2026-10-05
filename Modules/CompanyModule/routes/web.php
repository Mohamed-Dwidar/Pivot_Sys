<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\CompanyModule\app\Http\Controllers\Account\CompanyAccountModuleController;

/**
 *  Logged in account: companies list
 */
Route::prefix('account/companies')->name('account.companies.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('/', [CompanyAccountModuleController::class, 'index'])->name('index');
    Route::get('create', [CompanyAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [CompanyAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}', [CompanyAccountModuleController::class, 'show'])->name('show');
    Route::get('{id}/edit', [CompanyAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [CompanyAccountModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [CompanyAccountModuleController::class, 'destroy'])->name('destroy');
});
