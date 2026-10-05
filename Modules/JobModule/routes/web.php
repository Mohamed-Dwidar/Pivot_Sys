<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\JobModule\app\Http\Controllers\Account\JobAccountModuleController;

/**
 *  Logged in account: jobs list
 */
Route::prefix('account/jobs')->name('account.jobs.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('/', [JobAccountModuleController::class, 'index'])->name('index');
    Route::get('create', [JobAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [JobAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}', [JobAccountModuleController::class, 'show'])->name('show');
    Route::get('{id}/edit', [JobAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [JobAccountModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [JobAccountModuleController::class, 'destroy'])->name('destroy');
});
