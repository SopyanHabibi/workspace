<?php

namespace App\Http\Controllers;

use App\Support\TaskPriority;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskListController extends Controller
{
    public function index(Request $request)
    {
        $workspace = $request->user()->defaultWorkspace();

        $statusFilter = $request->string('status')->toString() ?: null;

        $tasks = $workspace->projects()
            ->with(['tasks' => function ($q) use ($statusFilter) {
                if ($statusFilter) {
                    $q->where('status', $statusFilter);
                }
            }])
            ->get()
            ->pluck('tasks')
            ->flatten()
            ->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'importance' => $task->importance,
                'due_at' => $task->due_at,
                'project_id' => $task->project_id,
                'project_name' => $task->project->name,
                'priority_level' => TaskPriority::level($task),
                'priority_score' => TaskPriority::score($task),
            ])
            ->sortByDesc('priority_score')
            ->values();

        return Inertia::render('tasks/Index', [
            'tasks' => $tasks,
            'statusFilter' => $statusFilter,
        ]);
    }
}