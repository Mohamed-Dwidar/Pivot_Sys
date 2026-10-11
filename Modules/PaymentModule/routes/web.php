<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\EmployeeModule\app\Models\Employee;
use Modules\PaymentModule\app\Http\Controllers\Account\DayClosingAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Account\ExtraAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Account\IncomeCategoryAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Account\PaymentAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Account\PaymentMethodAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Account\WithholdingCertificateAccountModuleController;
use Modules\PaymentModule\app\Http\Controllers\Employee\DayClosingEmployeeModuleController;
use Modules\PaymentModule\app\Http\Controllers\Employee\PaymentEmployeeModuleController;

/**
 *  Payments: added and viewed by the account and by its employees (same actions, see Concerns\PaymentActions);
 *  edit / delete / refund = the account only (step 6)
 */
$paymentRoutes = function ($controller) {
    Route::get('/', [$controller, 'index'])->name('index');
    Route::get('data', [$controller, 'data'])->name('data');
    // the member's reservations / packages drop menu of the form
    Route::get('options/payables', [$controller, 'payables'])->name('options.payables');
    Route::get('create', [$controller, 'create'])->name('create');
    Route::post('/', [$controller, 'store'])->name('store');
    Route::get('{id}', [$controller, 'show'])->name('show');
    Route::get('{id}/receipt', [$controller, 'receipt'])->name('receipt');
};

Route::prefix('account/payments')->name('account.payments.')->middleware(['auth', 'user.active:' . Account::class])
    ->group(fn () => $paymentRoutes(PaymentAccountModuleController::class));

// the owner only: edit / delete (with a reason) / refund
Route::prefix('account/payments')->name('account.payments.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('{id}/edit', [PaymentAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [PaymentAccountModuleController::class, 'update'])->name('update');
    Route::get('{id}/delete', [PaymentAccountModuleController::class, 'deleteForm'])->name('delete');
    Route::delete('{id}', [PaymentAccountModuleController::class, 'destroy'])->name('destroy');
    Route::get('{id}/refund', [PaymentAccountModuleController::class, 'refundForm'])->name('refund');
    Route::post('{id}/refund', [PaymentAccountModuleController::class, 'refund'])->name('refund.store');
});

Route::prefix('employee/payments')->name('employee.payments.')->middleware(['auth', 'user.active:' . Employee::class])
    ->group(fn () => $paymentRoutes(PaymentEmployeeModuleController::class));

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

/**
 *  Withholding tax certificates: only the account manages them
 */
Route::prefix('account/withholding-certificates')->name('account.withholding-certificates.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('/', [WithholdingCertificateAccountModuleController::class, 'index'])->name('index');
    Route::get('data', [WithholdingCertificateAccountModuleController::class, 'data'])->name('data');
    Route::get('create', [WithholdingCertificateAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [WithholdingCertificateAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}', [WithholdingCertificateAccountModuleController::class, 'show'])->name('show');
    Route::get('{id}/edit', [WithholdingCertificateAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [WithholdingCertificateAccountModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [WithholdingCertificateAccountModuleController::class, 'destroy'])->name('destroy');
    Route::get('{id}/attachment', [WithholdingCertificateAccountModuleController::class, 'attachment'])->name('attachment');
});

/**
 *  Day closing: the account and its employees close days (same actions, see Concerns\DayClosingActions); reopen = the account only
 */
$closingRoutes = function ($controller) {
    Route::get('/', [$controller, 'index'])->name('index');
    Route::get('data', [$controller, 'data'])->name('data');
    // ?date=: the day's totals + "Close this day"
    Route::get('create', [$controller, 'create'])->name('create');
    Route::post('/', [$controller, 'store'])->name('store');
    Route::get('{id}', [$controller, 'show'])->name('show');
};

Route::prefix('account/day-closings')->name('account.day-closings.')->middleware(['auth', 'user.active:' . Account::class])->group(function () use ($closingRoutes) {
    $closingRoutes(DayClosingAccountModuleController::class);
    Route::get('{id}/reopen', [DayClosingAccountModuleController::class, 'reopenForm'])->name('reopen');
    Route::post('{id}/reopen', [DayClosingAccountModuleController::class, 'reopen'])->name('reopen.store');
});

Route::prefix('employee/day-closings')->name('employee.day-closings.')->middleware(['auth', 'user.active:' . Employee::class])
    ->group(fn () => $closingRoutes(DayClosingEmployeeModuleController::class));

/**
 *  Package extras / adjustments: only the account manages them (from the package view)
 */
Route::prefix('account/extras')->name('account.extras.')->middleware(['auth', 'user.active:' . Account::class])->group(function () {
    Route::get('create', [ExtraAccountModuleController::class, 'create'])->name('create');
    Route::post('/', [ExtraAccountModuleController::class, 'store'])->name('store');
    Route::get('{id}/edit', [ExtraAccountModuleController::class, 'edit'])->name('edit');
    Route::put('{id}', [ExtraAccountModuleController::class, 'update'])->name('update');
    Route::delete('{id}', [ExtraAccountModuleController::class, 'destroy'])->name('destroy');
});
