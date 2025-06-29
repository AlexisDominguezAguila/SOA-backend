<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\admin\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Rate‑limit al login: máx. 10 peticiones por minuto
Route::post('authenticate', [AuthenticationController::class, 'authenticate'])
     ->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Rutas protegidas con Sanctum
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Ejemplo de endpoint para que el frontend obtenga el usuario actual
    Route::get('me', fn (Request $request) => $request->user());

    Route::get('dashboard', [DashboardController::class, 'index']);

    Route::post('logout', [AuthenticationController::class, 'logout']);
});
