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
        $projects = $request->user()->defaultWorkspace()->projects()
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            ])
            ->latest()
            ->get();

        return Inertia::render('projects/Index', ['projects' => $projects]);
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

        $project->load(['tasks' => fn ($q) => $q->orderBy('due_at')]);

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