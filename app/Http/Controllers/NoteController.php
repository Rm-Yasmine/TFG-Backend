<?php

namespace App\Http\Controllers;

use App\Services\NoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function notes()
    {
        $notes = $this->noteService->listByUser(Auth::id());
        return response()->json(['status' => 'success', 'data' => $notes]);
    }

    public function shared()
    {
        $notes = $this->noteService->listShared();
        return response()->json(['status' => 'success', 'data' => $notes]);
    }

    public function addNote(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();

        $note = $this->noteService->create($validated);

        return response()->json(['status' => 'success', 'data' => $note]);
    }

    public function updatenote(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'nullable|string',
            'is_shared' => 'boolean',
        ]);

        $note = $this->noteService->update($id, $validated);

        return response()->json(['status' => 'success', 'data' => $note]);
    }

    public function destroynote($id)
    {
        $this->noteService->delete($id);

        return response()->json(['status' => 'success', 'message' => 'Nota eliminada']);
    }
}
