<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\EmployeeModule\app\Models\Employee;
use Modules\MemberModule\app\Http\Controllers\Account\MemberAccountModuleController;
use Modules\MemberModule\app\Http\Controllers\Employee\MemberEmployeeModuleController;

/**
 *  Members: managed by the account and by its employees (same actions, see Concerns\MemberActions); delete = the account only
 */
$memberRoutes = function ($controller) {
    Route::get('/', [$controller, 'index'])->name('index');
    Route::get('data', [$controller, 'data'])->name('data');
    // the Member drop menus of the reservation / package / cash in / withholding forms: search + the quick add duplicate check
    Route::get('search', [$controller, 'search'])->name('search');
    Route::get('lookup', [$controller, 'lookup'])->name('lookup');
    Route::get('create', [$controller, 'create'])->name('create');
    Route::post('/', [$controller, 'store'])->name('store');
    Route::get('{id}', [$controller, 'show'])->name('show');
    Route::get('{id}/edit', [$controller, 'edit'])->name('edit');
    Route::put('{id}', [$controller, 'update'])->name('update');
};

Route::prefix('account/members')->name('account.members.')->middleware(['auth', 'user.active:' . Account::class])->group(function () use ($memberRoutes) {
    $memberRoutes(MemberAccountModuleController::class);
    Route::delete('{id}', [MemberAccountModuleController::class, 'destroy'])->name('destroy');
});

Route::prefix('employee/members')->name('employee.members.')->middleware(['auth', 'user.active:' . Employee::class])
    ->group(fn () => $memberRoutes(MemberEmployeeModuleController::class));
