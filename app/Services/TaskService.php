<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;

class TaskService
{
    protected $taskRepo;

    public function __construct(TaskRepository $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function list(array $filters = [])
    {
        return $this->taskRepo->all($filters);
    }

    public function getById($id)
    {
        return $this->taskRepo->find($id);
    }

    public function create(array $data)
    {
        return $this->taskRepo->create($data);
    }

    public function update($id, array $data)
    {
        return $this->taskRepo->update($id, $data);
    }

    public function updateStatus($id, string $status)
    {
        return $this->taskRepo->updateStatus($id, $status);
    }

    public function delete($id)
    {
        return $this->taskRepo->delete($id);
    }

    public function assignUser(int $taskId, ?int $assigneeId)
    {
        $task = Task::findOrFail($taskId);
        $task->assignee_id = $assigneeId;
        $task->save();

        return $task;
    }
}
