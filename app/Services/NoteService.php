<?php

namespace App\Services;

use App\Repositories\NoteRepository;

class NoteService
{
    protected $noteRepo;

    public function __construct(NoteRepository $noteRepo)
    {
        $this->noteRepo = $noteRepo;
    }

    public function listByUser($userId)
    {
        return $this->noteRepo->getByUser($userId);
    }

    public function listShared()
    {
        return $this->noteRepo->sharedNotes();
    }

    public function getById($id)
    {
        return $this->noteRepo->find($id);
    }

    public function create(array $data)
    {
        return $this->noteRepo->create($data);
    }

    public function update($id, array $data)
    {
        return $this->noteRepo->update($id, $data);
    }

    public function delete($id)
    {
        return $this->noteRepo->delete($id);
    }
}
