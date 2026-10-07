<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\EmployeeModule\app\Models\Employee;
use Modules\ReservationModule\app\Http\Controllers\Account\ReservationAccountModuleController;
use Modules\ReservationModule\app\Http\Controllers\Account\ReservationStatusAccountModuleController;
use Modules\ReservationModule\app\Http\Controllers\Employee\ReservationEmployeeModuleController;

/**
 *  Reservations: managed by the account and by its employees (same actions, see Concerns\ReservationActions)
 */
$reservationRoutes = function ($controller) {
    Route::get('/', [$controller, 'index'])->name('index');
    Route::get('data', [$controller, 'data'])->name('data');
    // the dependent drop menus of the form
    Route::get('options/spaces', [$controller, 'spaces'])->name('options.spaces');
    Route::get('options/units', [$controller, 'units'])->name('options.units');
    Route::get('options/plans', [$controller, 'plans'])->name('options.plans');
    Route::get('create', [$controller, 'create'])->name('create');
    Route::post('/', [$controller, 'store'])->name('store');
    Route::get('{id}', [$controller, 'show'])->name('show');
    Route::get('{id}/edit', [$controller, 'edit'])->name('edit');
    Route::put('{id}', [$controller, 'update'])->name('update');
    Route::delete('{id}', [$controller, 'destroy'])->name('destroy');
};

Route::prefix('account/reservations')->name('account.reservations.')->middleware(['auth', 'user.active:' . Account::class])
    ->group(fn () => $reservationRoutes(ReservationAccountModuleController::class));

Route::prefix('employee/reservations')->name('employee.reservations.')->middleware(['auth', 'user.active:' . Employee::class])
    ->group(fn () => $reservationRoutes(ReservationEmployeeModuleController::class));

/**
 *  Reservation statuses: only the account manages them
 */
Route::prefix('account/reservation-statuses')->name('account.reservation-statuses.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('/', [ReservationStatusAccountModuleController::class, 'index'])->name('index');
    Route::get('data', [ReservationStatusAccountModuleController::class, 'data'])->name('data');
    Route::get('create', [ReservationStatusAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [ReservationStatusAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}/edit', [ReservationStatusAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [ReservationStatusAccountModuleController::class, 'update'])->name('update');
    Route::patch('{id}/active', [ReservationStatusAccountModuleController::class, 'toggleActive'])->name('active');
    Route::patch('{id}/default', [ReservationStatusAccountModuleController::class, 'toggleDefault'])->name('default');
    Route::delete('{id}', [ReservationStatusAccountModuleController::class, 'destroy'])->name('destroy');
});
