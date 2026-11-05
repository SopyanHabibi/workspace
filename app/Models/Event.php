<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;
    protected $fillable = ['project_id', 'task_id', 'title', 'starts_at', 'ends_at'];
protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
public function project() { return $this->belongsTo(Project::class); }
public function task() { return $this->belongsTo(Task::class); }
}
