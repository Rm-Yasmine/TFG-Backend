<?php

namespace App\Services;

use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;

class ProjectService
{
    protected $projectRepo;
    protected $taskRepo;

    public function __construct(ProjectRepository $projectRepo, TaskRepository $taskRepo)
    {
        $this->projectRepo = $projectRepo;
        $this->taskRepo = $taskRepo;
    }

    public function listByUser($userId)
    {
        return $this->projectRepo->getByUser($userId);
    }

    public function getById($id)
    {
        return $this->projectRepo->find($id);
    }

    public function create(array $data)
    {
        $project = $this->projectRepo->create($data);

        // Crear tareas iniciales opcionales
        if (!empty($data['tasks'])) {
            foreach ($data['tasks'] as $taskData) {
                $taskData['project_id'] = $project->id;
                $taskData['created_by'] = $data['owner_id'];
                $this->taskRepo->create($taskData);
            }
        }

        return $project->load(['owner', 'members', 'tasks']);
    }

    public function update($id, array $data)
    {
        return $this->projectRepo->update($id, $data);
    }

    public function delete($id)
    {
        return $this->projectRepo->delete($id);
    }

    public function addMembers($projectId, array $userIds)
    {
        return $this->projectRepo->addMembers($projectId, $userIds);
    }
}
