<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// 1. RUTAS PÚBLICAS
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// RUTAS PROTEGIDAS (Requieren Token)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);

    // 2. RUTAS DE USUARIO NORMAL (Protegidas por Policy en el controlador)
    // El controlador UserController deberá usar $this->authorize('update', $user);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // 3. RUTAS DE ADMINISTRADOR (Protegidas por el middleware is_admin)
    Route::middleware('is_admin')->group(function () {
        // Ver todos los usuarios (usando el Resource)
        Route::get('/admin/users', [UserController::class, 'index']);
        // Bloquear/Desbloquear usuario
        Route::patch('/admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });
});
