<?php

namespace App\Http\Controllers;

use App\Models\ProjectMessage;
use App\Models\ProjectMessageRead;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class ProjectChatController extends Controller
{
     public function index($projectId)
    {
        return ProjectMessage::where('project_id', $projectId)
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();
    }

    public function store(Request $request, $projectId)
    {
        $request->validate(['message' => 'required|string']);

        return ProjectMessage::create([
            'project_id' => $projectId,
            'user_id' => auth()->Auth::id(),
            'message' => $request->message,
        ]);
    }

    public function markAsRead($projectId)
    {
        ProjectMessageRead::updateOrCreate(
            ['project_id' => $projectId, 'user_id' => Auth::id()],
            ['last_read_at' => Carbon::now()]
        );

        return response()->json(['ok' => true]);
    }

    public function unreadCount($projectId)
    {
        $read = ProjectMessageRead::where('project_id', $projectId)
            ->where('user_id', Auth::id())
            ->first();

        $query = ProjectMessage::where('project_id', $projectId)
            ->where('user_id', '!=', Auth::id());

        if ($read) {
            $query->where('created_at', '>', $read->last_read_at);
        }

        return ['unread' => $query->count()];
    }
}
