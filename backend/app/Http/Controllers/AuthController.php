<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario en el sistema.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique'    => 'Este correo electrónico ya está registrado.',
            'password.min'    => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol'      => 'cliente',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Registro exitoso. Bienvenido a VanguardiaShift.',
            'token'   => $token,
            'usuario' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'rol'   => $user->rol,
            ],
        ], 201);
    }

    /**
     * Autentica un usuario y devuelve un token de acceso.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'mensaje' => 'Credenciales incorrectas. Verificá tu email y contraseña.',
            ], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso.',
            'token'   => $token,
            'usuario' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'rol'   => $user->rol,
            ],
        ], 200);
    }

    /**
     * Revoca el token de acceso actual del usuario.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente.',
        ], 200);
    }

    /**
     * Devuelve los datos del usuario autenticado actualmente.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'usuario' => $request->user(),
        ], 200);
    }
}// VanguardiaShift - Autenticacion con Sanctum
