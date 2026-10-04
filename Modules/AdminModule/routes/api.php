<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminModule\app\Http\Controllers\AdminModuleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('adminmodules', AdminModuleController::class)->names('adminmodule');
});
