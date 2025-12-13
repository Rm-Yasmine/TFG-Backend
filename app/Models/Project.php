<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'start_date', 'end_date', 'owner_id'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
  
    public function timeSessions()
    {
        return $this->hasMany(TimeSession::class);
    }
}
