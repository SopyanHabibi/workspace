<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;
    protected $fillable = ['project_id', 'title', 'description', 'status', 'importance', 'due_at', 'completed_at'];
protected $casts = ['due_at' => 'datetime', 'completed_at' => 'datetime'];

public function project() { return $this->belongsTo(Project::class); }
public function notes() { return $this->hasMany(Note::class); }
public function events() { return $this->hasMany(Event::class); }
public function documents() { return $this->hasMany(Document::class); }
}
