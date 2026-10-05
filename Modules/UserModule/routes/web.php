<?php

use Illuminate\Support\Facades\Route;
use Modules\UserModule\app\Http\Controllers\UserModuleController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('login', [UserModuleController::class, 'loginForm'])->name('login');
    Route::post('login', [UserModuleController::class, 'login'])->name('loginUser');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [UserModuleController::class, 'logout'])->name('logout');

    Route::middleware('user.active')->group(function () {
        Route::get('my-account', [UserModuleController::class, 'editAccount'])->name('user.account.edit');
        Route::put('my-account', [UserModuleController::class, 'updateAccount'])->name('user.account.update');
    });
});
