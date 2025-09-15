<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Todas las rutas bajo /api
*/

// ------- Usuario autenticado (Sanctum) -------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ------- Auth -------
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// ------- Usuarios -------
Route::prefix('usuarios')->group(function () {
    Route::get('/listUsers',        [UsuarioController::class, 'index']);
    Route::post('/addUser',         [UsuarioController::class, 'store']);
    Route::get('/getUser/{id}',     [UsuarioController::class, 'show']);
    Route::put('/updateUser/{id}',  [UsuarioController::class, 'update']);
    Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']);
});

// ------- Tareas (REST con export) -------
// Importante: la ruta específica 'export' DEBE ir antes del resource para que no la capture {tarea}
Route::middleware('auth:sanctum')->group(function () {
    // Export (CSV) – primero para evitar colisión con /tareas/{tarea}
    Route::get('tareas/export', [TareaController::class, 'exportPendientes']);

    // Resource sólo con métodos que implementaste (index y store)
    Route::apiResource('tareas', TareaController::class)->only(['index','store']);
});
