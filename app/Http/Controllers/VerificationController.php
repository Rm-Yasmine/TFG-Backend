<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code' => 'required'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return ApiResponse::error('User not found', 404);
        }

        $record = EmailVerification::where('user_id', $user->id)
            ->where('code', $validated['code'])
            ->first();

        if (!$record)
            return ApiResponse::error('Invalid code', 400);

        if (Carbon::now()->greaterThan($record->expires_at))
            return ApiResponse::error('Code expired', 400);

        $user->email_verified_at = Carbon::now();
        $user->save();

        $record->delete();

        return ApiResponse::success(null, 'Email verified successfully');
    }
}

