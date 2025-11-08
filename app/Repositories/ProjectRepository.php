<?php

namespace App\Repositories;

use App\Models\Project;

class ProjectRepository
{
    public function getByUser($userId)
    {
        return Project::with(['owner', 'members', 'tasks'])
            ->where('owner_id', $userId)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $userId))
            ->get();
    }

    public function find($id)
    {
        return Project::with(['owner', 'members', 'tasks'])->findOrFail($id);
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
}
