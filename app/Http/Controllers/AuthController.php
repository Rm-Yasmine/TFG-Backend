<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Mail;


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
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        // Generar pin
        $code = rand(100000, 999999);

        // Crear usuario NO verificado
        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'verification_code' => $code,
        ]);

        // Enviar email
        Mail::raw("Tu código de verificación es: $code", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Código de verificación');
        });

        return response()->json(['message' => 'Usuario creado. Código enviado por email.']);
    }


    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->verification_code != $request->code) {
            return response()->json(['message' => 'Código incorrecto'], 400);
        }

        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->save();

        return response()->json(['message' => 'Correo verificado correctamente']);
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
}
