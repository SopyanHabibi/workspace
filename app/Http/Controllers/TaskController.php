<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'importance' => ['required', 'integer', 'between:1,3'],
            'due_at' => ['nullable', 'date'],
        ]);

        $project->tasks()->create($data);

        return back();
    }

    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['todo', 'in_progress', 'done'])],
            'importance' => ['sometimes', 'integer', 'between:1,3'],
            'due_at' => ['nullable', 'date'],
        ]);

        if (isset($data['status'])) {
            $data['completed_at'] = $data['status'] === 'done' ? now() : null;
        }

        $task->update($data);

// Auto-update project status berdasarkan task
$project = $task->project;
$total = $project->tasks()->count();
$done = $project->tasks()->where('status', 'done')->count();

if ($total > 0 && $done === $total) {
    $project->update(['status' => 'completed']);
} elseif ($project->status === 'completed') {
    $project->update(['status' => 'active']);
}

        if (isset($data['status']) && $data['status'] === 'done') {
    $task->project->workspace->user->activityLogs()->create([
        'action' => 'completed',
        'subject_type' => \App\Models\Task::class,
        'subject_id' => $task->id,
        'description' => "Menyelesaikan tugas \"{$task->title}\"",
    ]);
}

        return back();
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return back();
    }
}