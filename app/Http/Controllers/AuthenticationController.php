<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    /**
     * Iniciar sesión
     */
    public function authenticate(Request $request)
    {
        // 1. Validación de datos
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Errores de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2. Comprobar credenciales
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => false,
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        // 3. Generar token Sanctum (todas las habilidades '*')
        $user  = $request->user();                  // ya está autenticado
        $token = $user->createToken('auth_token', ['*'])->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Inicio de sesión exitoso',
            'data'    => [
                'token' => $token,
                'user'  => $user->only('id', 'name', 'email'),
            ],
        ], 200);
    }

    /**
     * Cerrar sesión (revoca únicamente el token actual)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Cierre de sesión exitoso',
        ], 200);
    }
}
