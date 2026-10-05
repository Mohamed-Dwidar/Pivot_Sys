<?php

use Illuminate\Support\Facades\Route;
use Modules\AccountModule\app\Models\Account;
use Modules\LayoutModule\app\Http\Controllers\Account\LayoutAccountModuleController;
use Modules\LayoutModule\app\Http\Controllers\Admin\LayoutAdminModuleController;

Route::get('admin/dashboard', [LayoutAdminModuleController::class, 'dashboard'])
    ->middleware('auth:admin')
    ->name('admin.dashboard');

Route::get('account/dashboard', [LayoutAccountModuleController::class, 'dashboard'])
    ->middleware(['auth', 'user.active:' . Account::class])
    ->name('account.dashboard');
