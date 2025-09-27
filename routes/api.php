<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TareaController;
use App\Http\Middleware\ResolveTenant;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Todas las rutas bajo /api.
| NOTA multitenant: envolvemos TODO con el middleware 'tenant' para que
| la BD se resuelva por subdominio ANTES de ejecutar cualquier controlador.
*/

Route::middleware(ResolveTenant::class)->group(function () {

    // ------- Auth (público) -------
    // Importante: el login también necesita tenant para buscar al usuario en la BD correcta.
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
        // Si no envían Authorization: Bearer <token> => 401
        Route::prefix('usuarios')->group(function () {
            Route::get('/listUsers',          [UsuarioController::class, 'index']);   // Listar
            Route::post('/addUser',           [UsuarioController::class, 'store']);   // Crear
            Route::get('/getUser/{id}',       [UsuarioController::class, 'show']);    // Ver uno
            Route::put('/updateUser/{id}',    [UsuarioController::class, 'update']);  // Actualizar
            Route::delete('/deleteUser/{id}', [UsuarioController::class, 'destroy']); // Eliminar
        });

        // ------- Tareas (PROTEGIDO) -------
        // Exportación (ruta específica ANTES del resource para evitar colisión con /tareas/{id})
        Route::get('tareas/export', [TareaController::class, 'exportPendientes']); // En formato de Excel
        // Solo index y store (los que se implementaron)
        Route::apiResource('tareas', TareaController::class)->only(['index','store']);
    });
});
