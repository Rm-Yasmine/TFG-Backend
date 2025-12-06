<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\ApiResponse;
use App\Mail\VerificationPinMail;
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
        'name' => 'required',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6'
    ]);

    $pin = rand(100000, 999999);

    cache()->put('register_' . $request->email, [
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'pin' => $pin
    ], now()->addMinutes(10));

    Mail::to($request->email)->send(new VerificationPinMail($pin));

    return ApiResponse::success(null, 'Código enviado al correo');
}


public function verifyPin(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'pin' => 'required'
    ]);

    $data = cache()->get('register_' . $request->email);

    if (!$data || $data['pin'] != $request->pin) {
        return ApiResponse::error('Código incorrecto o expirado', 400);
    }

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => $data['password'],
    ]);

    cache()->forget('register_' . $request->email);

    $token = $user->createToken('auth_token')->plainTextToken;

    return ApiResponse::success([
        'user' => $user,
        'token' => $token
    ], 'Usuario verificado y creado correctamente');
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
