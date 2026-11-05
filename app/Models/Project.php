<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;
    protected $fillable = ['workspace_id', 'name', 'description', 'status', 'due_date'];
protected $casts = ['due_date' => 'date'];

public function workspace() { return $this->belongsTo(Workspace::class); }
public function tasks() { return $this->hasMany(Task::class); }
public function notes() { return $this->hasMany(Note::class); }
public function events() { return $this->hasMany(Event::class); }
public function documents() { return $this->hasMany(Document::class); }
}
