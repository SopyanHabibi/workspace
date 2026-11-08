<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
{
    $statusFilter = $request->string('status')->toString() ?: null;

    $projects = $request->user()->defaultWorkspace()->projects()
        ->withCount([
            'tasks',
            'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            'tasks as in_progress_count' => fn ($q) => $q->where('status', 'in_progress'),
        ])
        ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
        ->latest()
        ->get();

    $allProjects = $request->user()->defaultWorkspace()->projects()
        ->withCount([
            'tasks',
            'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
        ])
        ->get();

    return Inertia::render('projects/Index', [
        'projects' => $projects,
        'statusFilter' => $statusFilter,
        'stats' => [
            'total' => $allProjects->count(),
            'in_progress' => $allProjects->where('status', 'active')->count(),
            'completed' => $allProjects->where('status', 'completed')->count(),
        ],
    ]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $request->user()->defaultWorkspace()->projects()->create($data);

        return to_route('projects.index');
    }

    public function show(Project $project)
{
    Gate::authorize('view', $project);

    $project->load([
        'tasks' => fn ($q) => $q->orderBy('due_at'),
        'notes' => fn ($q) => $q->latest(),
    ]);

    return Inertia::render('projects/Show', ['project' => $project]);
}

    public function update(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'completed', 'archived'])],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->update($data);

        return back();
    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return to_route('projects.index');
    }
}