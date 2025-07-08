<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    /**
     * Iniciar sesión y generar token Sanctum Bearer
     */
    public function authenticate(Request $request)
    {
        // 1. Validar (sin "exists" para evitar user‑enumeration)
        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Errores de validación',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 2. Intentar login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => false,
                'message' => 'Credenciales inválidas', // genérico
            ], 401);
        }

        // 3. Generar token
        $user        = $request->user();
        $accessToken = $user->createToken('auth_token', ['*'])->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Inicio de sesión exitoso',
            'data'    => [
                'access_token' => $accessToken,
                'token_type'   => 'Bearer',
                'user'         => $user->only('id', 'name', 'email'),
            ],
        ]);
    }

    /**
     * Cerrar sesión (revoca solo el token actual)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Cierre de sesión exitoso',
        ]);
    }
}
