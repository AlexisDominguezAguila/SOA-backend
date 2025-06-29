<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\admin\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('authenticate', [AuthenticationController::class, 'authenticate']);

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['middleware' => ['auth:sanctum']], function () {
    //proteccion de rutas
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::post('logout', [AuthenticationController::class, 'logout']);
});