<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSession extends Model
{
    
    protected $fillable = ['user_id', 'project_id', 'start_time', 'end_time'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
