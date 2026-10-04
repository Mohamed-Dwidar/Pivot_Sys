<?php

/**
 *  Admin Login
 */

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\app\Http\Controllers\Auth\AdminAuthController;



Route::get('/', [AdminAuthController::class, 'index'] )->name('admin.login');
Route::post('login', [AdminAuthController::class, 'login'])->name('admin.loginpost');



Route::prefix('admin')->group(function () {
    Route::get('/', [AdminAuthController::class, 'index'] )->name('admin.login');   //'Auth\AdminAuthController@index'
    // Route::get('/login', 'Auth\AdminAuthController@index')->name('admin.login');
    // Route::post('login', [AdminAuthController::class, 'login'])->name('admin.loginpost');

});


Route::group(['prefix'=>'admin'], function () {
    // Route::get('dashboard', 'AdminModuleController@dashboard')->name('admin.dashboard');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});