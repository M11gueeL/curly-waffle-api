<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Acceso denegado. Se requieren permisos de administrador.'], 403);
        }

        return $next($request);
    }
}