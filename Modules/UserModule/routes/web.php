<?php

use Illuminate\Support\Facades\Route;
use Modules\UserModule\app\Http\Controllers\Auth\UserAuthModuleController;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('login', [UserAuthModuleController::class, 'loginForm'])->name('login');
    Route::post('login', [UserAuthModuleController::class, 'login'])->name('loginUser');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [UserAuthModuleController::class, 'logout'])->name('logout');

    Route::middleware('user.active')->group(function () {
        Route::get('my-account', [UserAuthModuleController::class, 'editAccount'])->name('user.account.edit');
        Route::put('my-account', [UserAuthModuleController::class, 'updateAccount'])->name('user.account.update');
    });
});
