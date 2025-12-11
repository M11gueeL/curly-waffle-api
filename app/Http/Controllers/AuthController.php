<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // 1. Registro (Crea un usuario y devuelve token de una vez)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Creamos el token inmediatamente para que quede logueado
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // Registramos el inicio de sesión en el historial
        LoginHistory::create([
            'user_id' => $user->id,
            'token_id' => $tokenResult->accessToken->id,
            'login_at' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // 2. Login (verifica credenciales y devuelve token)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Buscamos el usuario por email
        $user = User::where('email', $request->email)->first();

        // Verifcamos si existe y si la contraseña es correcta
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // IMPORTANTE: Antes de borrar los tokens, cerramos las sesiones en el historial
        // para que no queden como "active" eternamente.
        LoginHistory::where('user_id', $user->id)
            ->where('status', 'active')
            ->update([
                'logout_at' => now(),
                'status' => 'closed', // O 'terminated_by_new_login'
            ]);

        // Generamos el nuevo token
        $tokenResult = $user->createToken('auth_token');
        $token = $tokenResult->plainTextToken;

        // guardamos el id del token en login_histories
        LoginHistory::create([
            'user_id' => $user->id,
            'token_id' => $tokenResult->accessToken->id,
            'login_at' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Hola ' . $user->name . ', has iniciado sesión exitosamente',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    // 3. Logout 
    public function logout(Request $request)
    {
        // 1. Obtenemos el token actual que se está usando
        $currentToken = $request->user()->currentAccessToken();

        // 2. Buscamos el historial que corresponde a ESTE token
        $history = LoginHistory::where('token_id', $currentToken->id)->where('status', 'active')
            ->first();

        if ($history) {
            $history->update([
                'logout_at' => now(),
                'status' => 'closed', // Cerrada voluntariamente
            ]);
        }

        // 3. Borramos el token (Revocamos acceso)
        $currentToken->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente.']);
    }

    // 4. Perfil del usuario autenticado
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

}