<?php

namespace App\Repositories;

use App\Models\Task;

class TaskRepository
{
    public function all($filters = [])
    {
        $query = Task::with(['project', 'assignee', 'creator']);

        if (!empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    public function find($id)
    {
        return Task::with(['project', 'assignee', 'creator'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Task::create($data);
    }

    public function update($id, array $data)
    {
        $task = Task::findOrFail($id);
        $task->update($data);
        return $task;
    }

    public function updateStatus($id, string $status)
    {
        $task = Task::findOrFail($id);
        $task->update(['status' => $status]);
        return $task;
    }

    public function delete($id)
    {
        return Task::destroy($id);
    }
}
