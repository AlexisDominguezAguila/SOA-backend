<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\DeanController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

// Login (con limitador de velocidad: máx. 10 peticiones por minuto)
Route::post('authenticate', [AuthenticationController::class, 'authenticate'])
     ->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Rutas protegidas con Sanctum
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('me', fn (Request $request) => $request->user());

    // Ejemplo de dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Logout
    Route::post('logout', [AuthenticationController::class, 'logout']);

    /*
    |----------------------------------------------------------------------
    | Recurso Decanos
    |----------------------------------------------------------------------
    | Endpoints generados (prefix /api/):
    |   GET    /deans           -> index
    |   POST   /deans           -> store
    |   GET    /deans/{dean}    -> show
    |   PUT    /deans/{dean}    -> update
    |   PATCH  /deans/{dean}    -> update
    |   DELETE /deans/{dean}    -> destroy
    */
    Route::apiResource('deans', DeanController::class);
});
