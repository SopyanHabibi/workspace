<?php

namespace App\Http\Controllers;

use App\Support\TaskPriority;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $workspace = $request->user()->defaultWorkspace();

        $projects = $workspace->projects()
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            ])
            ->get();

        $upcomingTasks = $workspace->projects()
            ->with('tasks')
            ->get()
            ->pluck('tasks')
            ->flatten()
            ->where('status', '!=', 'done')
            ->sortByDesc(fn ($task) => TaskPriority::score($task))
            ->take(5)
            ->values()
            ->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'project_id' => $task->project_id,
                'project_name' => $task->project->name,
                'importance' => $task->importance,
                'due_at' => $task->due_at,
                'priority_level' => TaskPriority::level($task),
            ]);

        $recentActivity = $workspace->user->activityLogs()
            ->latest()
            ->take(8)
            ->get(['action', 'description', 'created_at']);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_projects' => $projects->count(),
                'total_tasks' => $projects->sum('tasks_count'),
                'done_tasks' => $projects->sum('done_tasks_count'),
            ],
            'projects' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'status' => $p->status,
                'tasks_count' => $p->tasks_count,
                'done_tasks_count' => $p->done_tasks_count,
            ]),
            'upcomingTasks' => $upcomingTasks,
            'recentActivity' => $recentActivity,
        ]);
    }
}