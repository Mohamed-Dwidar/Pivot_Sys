<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\app\Http\Controllers\Admin\AdminAdminModuleController;
use Modules\AdminModule\app\Http\Controllers\Auth\AdminAuthModuleController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/', [AdminAuthModuleController::class, 'index'])->name('login');
        Route::post('login', [AdminAuthModuleController::class, 'login'])->name('loginpost');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthModuleController::class, 'logout'])->name('logout');
        Route::get('profile', [AdminAdminModuleController::class, 'editProfile'])->name('profile.edit');
        Route::put('profile', [AdminAdminModuleController::class, 'updateProfile'])->name('profile.update');
    });
});
