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

    /*
    misproyectos() → listar proyectos del usuario (propios y colaboraciones)
    */
    public function misproyectos(Request $request)
    {
        $projects = $this->service->listByUser($request->user()->id);
        return ApiResponse::success($projects, 'Projects loaded successfully');
    }

    /* 
    addproyectos → crear proyecto con tareas iniciales
    */
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

    /*
    showproyecto($id) → ver detalles del proyecto 
    */
    public function showproyecto($id)
    {
        $project = $this->service->getById($id);
        return ApiResponse::success($project, 'Project details loaded');
    }

    /* 
    updateproyecto($id) → actualizar proyecto
    */
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

    /* 
    deleteproyecto($id) → eliminar proyecto
    */
    public function eliminarproyecto($id)
    {
        $this->service->delete($id);
        return ApiResponse::success(null, 'Project deleted successfully');
    }

    /* 
    addMembers($id) → agregar miembros al proyecto
    */
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
