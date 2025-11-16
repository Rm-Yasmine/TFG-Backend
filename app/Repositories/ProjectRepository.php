<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectRepository
{
    public function getOwnProjects($userId)
    {
        return Project::with(['owner', 'members', 'tasks.assignee'])
            ->where('owner_id', $userId)
            ->get();
    }

    public function getCollaborationProjects($userId)
    {
        return Project::with(['owner', 'members', 'tasks.assignee'])
            ->whereHas('tasks', function ($q) use ($userId) {
                $q->where('assignee_id', $userId);
            })
            ->where('owner_id', '!=', $userId)
            ->get();
    }

    public function find($id)
    {
        return Project::with(['owner', 'members', 'tasks.assignee'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Project::create($data);
    }

    public function update($id, array $data)
    {
        $project = Project::findOrFail($id);
        $project->update($data);
        return $project;
    }

    public function delete($id)
    {
        return Project::destroy($id);
    }

    public function addMembers($id, array $userIds)
    {
        $project = Project::findOrFail($id);
        $project->members()->syncWithoutDetaching($userIds);
        return $project->members;
    }
    
    public function getByEndDateAscForUser($userId)
    {
        return Project::where('owner_id', $userId)
            ->orderBy('end_date', 'asc')
            ->get();
    }
}
