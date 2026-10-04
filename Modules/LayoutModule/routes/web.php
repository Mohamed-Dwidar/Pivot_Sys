<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\LayoutModule\app\Http\Controllers\LayoutModuleController;

// Route::get('/', [LayoutModuleController::class, 'home_page'])->name('home_page');

Route::group(['prefix' => 'admin', 'middleWare' => 'auth'], function () {
    Route::get('/dashboard', [LayoutModuleController::class, 'admin_dashboard'])->name('dashboard.index');
});