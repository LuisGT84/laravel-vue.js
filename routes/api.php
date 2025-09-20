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
| Todas las rutas disponibles bajo /api
| - Login queda público (no requiere token)
| - El resto va dentro de auth:sanctum (requiere token) => 401 si no lo envían
*/

// ------- Auth (público) -------
Route::post('/login', [AuthController::class, 'login']); // Devuelve token Sanctum

// ------- Grupo protegido por Sanctum (token requerido) -------
Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado (útil para verificar sesión desde el front)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cerrar sesión (revoca token)
    Route::post('/logout', [AuthController::class, 'logout']);

    // ------- CRUD de Usuarios (PROTEGIDO) -------
    // Si alguna petición no incluye Authorization: Bearer <token>, devuelve 401
    Route::prefix('usuarios')->group(function () {
        Route::get('/listUsers',          [UsuarioController::class, 'index']);   // Listar
        Route::post('/addUser',           [UsuarioController::class, 'store']);   // Crear
        Route::get('/getUser/{id}',       [UsuarioController::class, 'show']);    // Ver uno
        Route::put('/updateUser/{id}',    [UsuarioController::class, 'update']);  // Actualizar
        Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']); // Eliminar
    });

    // ------- Tareas (PROTEGIDO) -------
    // Exportación a Excel (ruta específica ANTES que resource para evitar colisión con /tareas/{id})
    Route::get('tareas/export', [TareaController::class, 'exportPendientes']); // XLSX
    // Solo index y store (los que implementaste)
    Route::apiResource('tareas', TareaController::class)->only(['index','store']);
});
// Nota: las rutas de Sanctum (login, logout, csrf-cookie) ya están definidas en vendor/laravel/sanctum/routesS