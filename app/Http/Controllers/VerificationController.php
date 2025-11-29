<?php

use App\Http\Controllers\Controller;
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
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $record = EmailVerification::where('user_id', $user->id)
            ->where('code', $validated['code'])
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Código incorrecto'], 400);
        }

        if (Carbon::now()->greaterThan($record->expires_at)) {
            return response()->json(['message' => 'Código expirado'], 400);
        }

        // Marcar usuario como verificado
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Eliminar el código
        $record->delete();

        return response()->json([
            'message' => 'Email verificado correctamente'
        ], 200);
    }
}
