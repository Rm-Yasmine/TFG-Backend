<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeSession;
use Illuminate\Support\Facades\Auth;

class TimeSessionController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
        ]);

        // Evitar múltiples sesiones activas
        $active = TimeSession::where('user_id', Auth::id())
            ->whereNull('end_time')
            ->first();

        if ($active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya tienes una sesión activa'
            ], 422);
        }

        $session = TimeSession::create([
            'user_id' => Auth::id(),
            'project_id' => $request->project_id,
            'start_time' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $session->load('project')
        ]);
    }

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
            return response()->json([
                'status' => 'error',
                'message' => 'Sesión no encontrada o ya detenida'
            ], 404);
        }

        $session->update([
            'end_time' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $session->load('project')
        ]);
    }

    public function timesession()
    {
        $sessions = TimeSession::with('project')
            ->where('user_id', Auth::id())
            ->orderByDesc('start_time')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sessions
        ]);
    }
}
