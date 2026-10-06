<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\SpaceModule\app\Http\Controllers\Account\SpaceAccountModuleController;
use Modules\SpaceModule\app\Http\Controllers\Account\SubscriptionTypeAccountModuleController;

/**
 *  Logged in account: spaces + subscription types
 */
Route::prefix('account')->name('account.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::prefix('spaces')->name('spaces.')->group(function () {
        Route::get('/', [SpaceAccountModuleController::class, 'index'])->name('index');
        Route::get('data', [SpaceAccountModuleController::class, 'data'])->name('data');
        Route::get('create', [SpaceAccountModuleController::class, 'create'])->name('create');
        Route::post('/', [SpaceAccountModuleController::class, 'store'])->name('store');
        Route::get('{id}', [SpaceAccountModuleController::class, 'show'])->name('show');
        Route::get('{id}/edit', [SpaceAccountModuleController::class, 'edit'])->name('edit');
        Route::put('{id}', [SpaceAccountModuleController::class, 'update'])->name('update');
        Route::patch('{id}/active', [SpaceAccountModuleController::class, 'toggleActive'])->name('active');
        Route::delete('{id}', [SpaceAccountModuleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('subscription-types')->name('subscription-types.')->group(function () {
        Route::get('/', [SubscriptionTypeAccountModuleController::class, 'index'])->name('index');
        Route::get('data', [SubscriptionTypeAccountModuleController::class, 'data'])->name('data');
        Route::get('create', [SubscriptionTypeAccountModuleController::class, 'create'])->name('create');
        Route::post('/', [SubscriptionTypeAccountModuleController::class, 'store'])->name('store');
        Route::get('{id}', [SubscriptionTypeAccountModuleController::class, 'show'])->name('show');
        Route::get('{id}/edit', [SubscriptionTypeAccountModuleController::class, 'edit'])->name('edit');
        Route::put('{id}', [SubscriptionTypeAccountModuleController::class, 'update'])->name('update');
        Route::delete('{id}', [SubscriptionTypeAccountModuleController::class, 'destroy'])->name('destroy');
    });
});
