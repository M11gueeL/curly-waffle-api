<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

// 1. RUTAS PÚBLICAS
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Verificación de correo usando URL firmada (Hacer click desde el correo no envía token de auth normalmente)
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');

// RUTAS PROTEGIDAS (Requieren Token)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // Reenviar enlace de verificación de correo
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // 2. RUTAS DE USUARIO NORMAL (Protegidas por Policy en el controlador)
    // El controlador UserController deberá usar $this->authorize('update', $user);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    Route::post('/users/{user}/profile-picture', [UserController::class, 'updateProfilePicture']);

    // 3. RUTAS DE ADMINISTRADOR (Protegidas por el middleware is_admin)
    Route::middleware('is_admin')->group(function () {
        // Ver todos los usuarios (usando el Resource)
        Route::get('/admin/users', [UserController::class, 'index']);
        // Bloquear/Desbloquear usuario
        Route::patch('/admin/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });
});
