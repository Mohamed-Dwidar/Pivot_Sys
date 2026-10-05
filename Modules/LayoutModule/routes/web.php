<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\LayoutModule\app\Http\Controllers\LayoutModuleController;

Route::get('admin/dashboard', [LayoutModuleController::class, 'adminDashboard'])
    ->middleware('auth:admin')
    ->name('admin.dashboard');

Route::get('account/dashboard', [LayoutModuleController::class, 'accountDashboard'])
    ->middleware(['auth', 'user.active:' . Account::class])
    ->name('account.dashboard');
