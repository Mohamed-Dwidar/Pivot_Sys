<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\app\Http\Controllers\Admin\AdminController;
use Modules\AdminModule\app\Http\Controllers\Auth\AdminAuthController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/', [AdminAuthController::class, 'index'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('loginpost');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('profile', [AdminController::class, 'editProfile'])->name('profile.edit');
        Route::put('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    });
});
