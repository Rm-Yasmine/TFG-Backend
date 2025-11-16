<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeSession;
use Illuminate\Support\Facades\Auth;

class TimeSessionController extends Controller
{
    // Iniciar sesión de tiempo
    public function start(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        $session = TimeSession::create([
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'start_time' => now(),
        ]);

        return response()->json(['status' => 'success', 'data' => $session]);
    }

    // Detener sesión de tiempo
    public function stop(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:time_sessions,id',
        ]);

        $session = TimeSession::where('id', $request->session_id)
            ->where('user_id', Auth::id())
            ->whereNull('end_time')
            ->first();

        if (!$session) {
            return response()->json(['status' => 'error', 'message' => 'Sesión no encontrada o ya detenida'], 404);
        }

        $session->end_time = now();
        $session->save();

        return response()->json(['status' => 'success', 'data' => $session]);
    }

    // Listar historial de sesiones
    public function index()
    {
        $sessions = TimeSession::with('project')
            ->where('user_id', Auth::id())
            ->orderByDesc('start_time')
            ->get();

        return response()->json(['status' => 'success', 'data' => $sessions]);
    }
}
