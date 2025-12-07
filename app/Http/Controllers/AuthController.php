<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyCodeMail;




class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     $user = User::create([
    //         'name' => $validated['name'],
    //         'email' => $validated['email'],
    //         'password' => Hash::make($validated['password']),
    //     ]);

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return ApiResponse::success([
    //         'user' => $user,
    //         'token' => $token,
    //     ], 'User registered successfully');
    // }

    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6'
    ]);

    // 1️⃣ Crear usuario como NO verificado
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'email_verified_at' => null,
    ]);

    // 2️⃣ Crear PIN de verificación
    $code = rand(100000, 999999);

    DB::table('verification_codes')->updateOrInsert(
        ['email' => $request->email],
        [
            'code' => $code,
            'created_at' => now()
        ]
    );

    // 3️⃣ Enviar correo
    Mail::to($request->email)->send(new VerifyCodeMail($code));

    return response()->json([
        'status' => 'success',
        'message' => 'Código enviado al correo'
    ]);
}


    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => $user,
            'token' => $token,
        ], 'User logged in successfully');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(Request $request)
    {
        return ApiResponse::success($request->user(), 'Authenticated user data');
    }

    public function requestReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Email no encontrado'], 404);
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Email válido, procede al cambio de contraseña',
            'token' => $token    
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $entry = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$entry) {
            return response()->json(['status' => 'error', 'message' => 'Token inválido'], 400);
        }

        if (!Hash::check($request->token, $entry->token)) {
            return response()->json(['status' => 'error', 'message' => 'Token incorrecto'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    public function verifyCode(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|digits:6'
    ]);

    $record = DB::table('verification_codes')
        ->where('email', $request->email)
        ->where('code', $request->code)
        ->first();

    if (!$record) {
        return response()->json([
            'status' => 'error',
            'message' => 'Código incorrecto'
        ], 400);
    }

    // Verificar usuario
    $user = User::where('email', $request->email)->first();
    $user->email_verified_at = now();
    $user->save();

    // Borrar el código para que no se reutilice
    DB::table('verification_codes')->where('email', $request->email)->delete();

    // Login automático
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Cuenta verificada',
        'token' => $token,
        'user' => $user
    ]);
}

}
