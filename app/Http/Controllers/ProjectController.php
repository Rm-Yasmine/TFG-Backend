<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;
use App\Helpers\ApiResponse;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class ProjectController extends Controller
{
    protected $service;

    public function __construct(ProjectService $service)
    {
        $this->service = $service;
    }


    public function misproyectos(Request $request)
    {
        $projects = $this->service->listByUser($request->user()->id);
        return ApiResponse::success($projects, 'Projects loaded successfully');
    }


    public function addproyectos(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'owner_id' => 'required|exists:users,id',
            'tasks' => 'nullable|array',
        ]);

        $project = $this->service->create($validated);
        return ApiResponse::success($project, 'Project created successfully', Response::HTTP_CREATED);
    }


    public function showproyecto($id)
    {
        $project = $this->service->getById($id);

        return ApiResponse::success($project, 'Project details loaded');
    }



    public function updateproyecto(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = $this->service->update($id, $validated);
        return ApiResponse::success($project, 'Project updated successfully');
    }


    public function eliminarproyecto($id)
    {
        $this->service->delete($id);
        return ApiResponse::success(null, 'Project deleted successfully');
    }


    public function addMemberByEmail(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        $project = Project::findOrFail($id);
        $project->members()->syncWithoutDetaching([$user->id]);

        return ApiResponse::success($project->members, "Member added successfully");
    }


    public function sortedByEndDate()
    {
        $userId = Auth::id();
        $projects = $this->service->getSortedByEndDateForUser($userId);

        return response()->json([
            'status' => 'success',
            'data' => $projects
        ]);
    }
}
