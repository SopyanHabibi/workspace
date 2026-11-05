<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;
    protected $fillable = ['project_id', 'task_id', 'name', 'path', 'mime_type', 'size'];
public function project() { return $this->belongsTo(Project::class); }
public function task() { return $this->belongsTo(Task::class); }
}
