<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Services\TaskService;
use App\Helpers\ApiResponse;
use App\Models\Task;
use Illuminate\Http\Request;

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
            'status' => 'required|string',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Tarea creada correctamente',
            'data' => $task
        ], 201);
    }

    public function showtask($id)
    {
        $task = $this->service->getById($id);
        return ApiResponse::success($task, 'Task details loaded');
    }

    public function updatetask(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', TaskStatus::values()),
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $task
        ]);
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
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $task = Task::findOrFail($taskId);

        $task->assignee_id = $validated['assignee_id'];
        $task->save();

        return ApiResponse::success($task, 'Usuario asignado correctamente');
    }
}
