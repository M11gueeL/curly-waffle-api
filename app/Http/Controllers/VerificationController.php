<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class VerificationController extends Controller
{
    /**
     * Verificar el correo electrónico usando la URL firmada.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Enlace de verificación inválido o modificado.'], 403);
        }

        if (! $request->hasValidSignature()) {
             return response()->json(['message' => 'El enlace de verificación expiró o es inválido.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Tu correo ya ha sido verificado anteriormente.'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Correo modificado/verificado exitosamente.'], 200);
    }

    /**
     * Reenviar el enlace de verificación al usuario autenticado.
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Tu correo ya ha sido verificado.'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Enlace de verificación reenviado a tu correo.'], 200);
    }
}
