<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\UnitModule\app\Http\Controllers\Account\UnitAccountModuleController;
use Modules\UnitModule\app\Http\Controllers\Admin\ColorAdminModuleController;

/**
 *  Logged in account: units under a space (no separate list, they are shown on the space page)
 */
Route::prefix('account/spaces/{spaceId}/units')->name('account.spaces.units.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('data', [UnitAccountModuleController::class, 'data'])->name('data');
    Route::get('create', [UnitAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [UnitAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}', [UnitAccountModuleController::class, 'show'])->name('show');
    Route::get('{id}/edit', [UnitAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [UnitAccountModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [UnitAccountModuleController::class, 'destroy'])->name('destroy');
});

/**
 *  Admin: colors
 */
Route::prefix('admin/colors')->name('admin.colors.')->middleware('auth:admin')->group(function () {
    Route::get('/', [ColorAdminModuleController::class, 'index'])->name('index');
    Route::get('data', [ColorAdminModuleController::class, 'data'])->name('data');
    Route::get('create', [ColorAdminModuleController::class, 'create'])->name('create');
    Route::post('/', [ColorAdminModuleController::class, 'store'])->name('store');
    Route::get('{id}/edit', [ColorAdminModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [ColorAdminModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [ColorAdminModuleController::class, 'destroy'])->name('destroy');
});
