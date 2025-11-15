<?php

namespace App\Repositories;

use App\Models\Note;

class NoteRepository
{
    public function getByUser($userId)
    {
        return Note::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function find($id)
    {
        return Note::findOrFail($id);
    }

    public function create(array $data)
    {
        return Note::create($data);
    }

    public function update($id, array $data)
    {
        $note = Note::findOrFail($id);
        $note->update($data);
        return $note;
    }

    public function delete($id)
    {
        return Note::destroy($id);
    }

    public function sharedNotes()
    {
        return Note::where('is_shared', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
