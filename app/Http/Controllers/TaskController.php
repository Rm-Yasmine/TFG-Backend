<?php

namespace App\Http\Controllers;

use App\Services\TaskService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function tareas(Request $request)
    {
        $filters = $request->only(['assignee_id', 'project_id', 'status']);
        $tasks = $this->service->list($filters);
        return ApiResponse::success($tasks, 'Tasks loaded successfully');
    }

    public function addtareas(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:PENDING,IN_PROGRESS,COMPLETED',
            'assignee_id' => 'nullable|exists:users,id',
            'created_by' => 'required|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = $this->service->create($validated);
        return ApiResponse::success($task, 'Task created successfully', Response::HTTP_CREATED);
    }

    public function showtask($id)
    {
        $task = $this->service->getById($id);
        return ApiResponse::success($task, 'Task details loaded');
    }

    public function updatetask(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:PENDING,IN_PROGRESS,COMPLETED',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = $this->service->update($id, $validated);
        return ApiResponse::success($task, 'Task updated successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:PENDING,IN_PROGRESS,COMPLETED',
        ]);

        $task = $this->service->updateStatus($id, $validated['status']);
        return ApiResponse::success($task, 'Task status updated');
    }

    public function destroytask($id)
    {
        $this->service->delete($id);
        return ApiResponse::success(null, 'Task deleted successfully');
    }

    public function assignUser(Request $request, $taskId)
    {
        $validated = $request->validate([
            'assignee_id' => 'required|exists:users,id',
        ]);

        $task = $this->service->assignUser($taskId, $validated['assignee_id']);
        return ApiResponse::success($task, 'User assigned to task successfully');
    }
}
