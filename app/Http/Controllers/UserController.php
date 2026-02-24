<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function store(Request $request)
    {
        // validamos que los datos vengan bien
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone'      => ['nullable', 'string', 'unique:users', 'regex:/^\+[1-9]\d{1,14}$/'],
            'password' => 'required|string|min:8|confirmed',
            'birth_date' => 'nullable|date|before:today',
            
        ]);

        // Creamos el usuario usando el modelo User
        $user = User::create([
            'name'       => $request->name,
            'last_name'  => $request->last_name,
            'username'   => $request->username,
            'email'      => $request->email,
            'birth_date' => $request->birth_date,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
        ]);

        // respondemos con el usuario creado
        return response()->json($user, 201);
    }

    // best practice: inyectar el modelo User directamente en vez de buscar por id manualmente
    public function show(User $user)
    {
        // laravel busca el usuario por id automáticamente, si llegamos aqui es porque existe
        return response()->json($user);
        
    }

    public function update(Request $request, User $user)
    {
        // 1. validamos los datos, usamos 'sometimes' para soportar Patch 
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|required|string|min:8',
        ]);

        // 2. actualizamos los campos si vienen en la request
        // request->all(); toma todo, pero user::update() solo actualiza los campos que están en fillable
        $user->update($request->all());

        // 3. si viene password, lo hasheamos antes de guardar
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }   

        // 4. respondemos con el usuario actualizado
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
