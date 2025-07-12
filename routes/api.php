<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Api\DeanController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\CursoController; 
use App\Http\Controllers\Api\ComunicadoController;
use App\Http\Controllers\Api\ConvenioController;


/*
|--------------------------------------------------------------------------
| Rutas públicas ─── SIN autenticación
|--------------------------------------------------------------------------
*/

// Login 
Route::post('authenticate', [AuthenticationController::class, 'authenticate'])
     ->middleware('throttle:10,1');

/* ───── PÚBLICAS ──────────────────────────────────────────────────────── */
// Devuelve solo decanos activos
Route::get('public/deans', [DeanController::class, 'publicIndex']);

// Devuelve solo noticias activas            
Route::get('public/news',  [NewsController::class, 'publicIndex']);

// Devuelve solo cursos activos 
Route::get('public/cursos', [CursoController::class, 'publicIndex']);

// Devuelve solo comunicados activos
Route::get('public/comunicados', [ComunicadoController::class, 'publicIndex']);

//Devuelve solo convenios activos
Route::get('public/convenios', [ConvenioController::class, 'publicIndex']);


/*
|--------------------------------------------------------------------------
| Rutas protegidas con Sanctum
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('me', fn (Request $request) => $request->user());

    // Dashboard de administración
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Logout
    Route::post('logout', [AuthenticationController::class, 'logout']);

    /*
    |----------------------------------------------------------------------|
    |  Recursos protegidos
    |----------------------------------------------------------------------|
    */
    Route::apiResource('deans', DeanController::class);
    Route::apiResource('news',  NewsController::class);   
    Route::apiResource('cursos', CursoController::class);
    Route::apiResource('comunicados', ComunicadoController::class);
    Route::apiResource('convenios', ConvenioController::class);

});