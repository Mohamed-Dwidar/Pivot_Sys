<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\MemberModule\app\Http\Controllers\Account\MemberAccountModuleController;

/**
 *  Logged in account: members
 */
Route::prefix('account/members')->name('account.members.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('/', [MemberAccountModuleController::class, 'index'])->name('index');
    Route::get('data', [MemberAccountModuleController::class, 'data'])->name('data');
    Route::get('create', [MemberAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [MemberAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}', [MemberAccountModuleController::class, 'show'])->name('show');
    Route::get('{id}/edit', [MemberAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [MemberAccountModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [MemberAccountModuleController::class, 'destroy'])->name('destroy');
});
