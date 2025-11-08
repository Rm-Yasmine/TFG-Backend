<?php

namespace App\Http\Controllers;

use App\Services\ProjectService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    protected $service;

    public function __construct(ProjectService $service)
    {
        $this->service = $service;
    }

    // index() → listar proyectos (donde soy owner o miembro)
    public function index(Request $request)
    {
        $projects = $this->service->listByUser($request->user()->id);
        return ApiResponse::success($projects, 'Projects loaded successfully');
    }

    // store() → crear nuevo proyecto (con tareas opcional)
    public function proyectos(Request $request)
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

    // show($id) → ver detalles con miembros y tareas
    public function show($id)
    {
        $project = $this->service->getById($id);
        return ApiResponse::success($project, 'Project details loaded');
    }

    // update($id) → editar datos del proyecto
    public function update(Request $request, $id)
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

    // destroy($id) → eliminar proyecto
    public function destroy($id)
    {
        $this->service->delete($id);
        return ApiResponse::success(null, 'Project deleted successfully');
    }

    // addMembers($id) → añadir miembros
    public function addMembers(Request $request, $id)
    {
        $validated = $request->validate([
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        $members = $this->service->addMembers($id, $validated['members']);
        return ApiResponse::success($members, 'Members added successfully');
    }
}
