<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Http\Requests\ResendCodeRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmailCode;




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
    /* -------------------------------------------
        EMAIL VERIFICATION
    ------------------------------------------- */

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        // Código de verificación
        $code = rand(100000, 999999);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'verification_code' => $code
        ]);

        // Enviar email
        Mail::to($user->email)->send(new VerifyEmailCode($code));

        return response()->json([
            'message' => 'Registro correcto. Se envió un código a tu correo.',
            'email' => $user->email,
        ]);
    }

    public function verifyCode(VerifyCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) return response()->json(['message' => 'Usuario no existe'], 404);

        if ($user->verification_code != $request->code) {
            return response()->json(['message' => 'Código incorrecto'], 400);
        }

        // Verificar
        $user->verification_code = null;
        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Cuenta verificada correctamente.']);
    }

    public function resendCode(ResendCodeRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $code = rand(100000, 999999);

        $user->verification_code = $code;
        $user->save();

        Mail::to($user->email)->send(new VerifyEmailCode($code));

        return response()->json(['message' => 'Código reenviado.']);
    }

}
