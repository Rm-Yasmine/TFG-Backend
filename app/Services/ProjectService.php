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
        $ownProjects = $this->projectRepo->getOwnProjects($userId);
        $collaborationProjects = $this->projectRepo->getCollaborationProjects($userId);

        // Calcular progreso
        $ownProjects->each(function ($project) use ($userId) {
            $this->calculateProgress($project, $userId);
        });

        $collaborationProjects->each(function ($project) use ($userId) {
            $this->calculateProgress($project, $userId);
        });

        return [
            'own' => $ownProjects,
            'collaboration' => $collaborationProjects
        ];
    }

    public function getById($id)
    {
        $project = $this->projectRepo->find($id);
        $this->calculateProgress($project, $project->owner_id);
        return $project;
    }

    protected function calculateProgress($project, $userId)
    {
        $tasks = $project->tasks;

        if ($tasks->isEmpty()) {
            $project->progress = 0;
            return;
        }

        if ($project->owner_id === $userId) {
            // Dueña: calcula progreso global
            $completed = $tasks->where('status', 'COMPLETED')->count();
            $total = $tasks->count();
        } else {
            // Colaboradora: solo sus tareas
            $userTasks = $tasks->where('assignee_id', $userId);
            $completed = $userTasks->where('status', 'COMPLETED')->count();
            $total = $userTasks->count();
        }

        $project->progress = $total > 0 ? round(($completed / $total) * 100) : 0;
    }

    public function create(array $data)
    {
        $project = $this->projectRepo->create($data);

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

    public function getSortedByEndDateForUser($userId)
    {
        return $this->projectRepo->getByEndDateAscForUser($userId);
    }
}
