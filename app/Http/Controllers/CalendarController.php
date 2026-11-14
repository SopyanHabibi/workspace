<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $workspace = $request->user()->defaultWorkspace();

        // Events dari tabel events
        $events = $workspace->projects()
            ->with(['events' => fn ($q) => $q->whereBetween('starts_at', [$start, $end])])
            ->get()
            ->pluck('events', 'name')
            ->map(fn ($events, $projectName) => $events->map(fn ($e) => [
                'id'         => $e->id,
                'title'      => $e->title,
                'date'       => Carbon::parse($e->starts_at)->toDateString(),
                'starts_at'  => $e->starts_at,
                'ends_at'    => $e->ends_at,
                'type'       => 'event',
                'project'    => $projectName,
            ]))
            ->flatten(1)
            ->values();

        // Deadline task
        $taskDeadlines = $workspace->projects()
            ->with(['tasks' => fn ($q) => $q
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [$start, $end])
                ->where('status', '!=', 'done')
            ])
            ->get()
            ->pluck('tasks', 'name')
            ->map(fn ($tasks, $projectName) => $tasks->map(fn ($t) => [
                'id'        => $t->id,
                'title'     => $t->title,
                'date'      => Carbon::parse($t->due_at)->toDateString(),
                'starts_at' => $t->due_at,
                'ends_at'   => null,
                'type'      => 'deadline',
                'project'   => $projectName,
            ]))
            ->flatten(1)
            ->values();

        $projects = $workspace->projects()->get(['id', 'name']);

        return Inertia::render('calendar/Index', [
            'year'    => $year,
            'month'   => $month,
            'entries' => $events->merge($taskDeadlines)->values(),
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'exists:projects,id'],
            'starts_at'  => ['required', 'date'],
            'ends_at'    => ['nullable', 'date', 'after_or_equal:starts_at'],
            'task_id'    => ['nullable', 'exists:tasks,id'],
        ]);

        $project = Project::findOrFail($data['project_id']);
        Gate::authorize('update', $project);

        $project->events()->create($data);

        return back();
    }

    public function destroy(Event $event)
    {
        Gate::authorize('update', $event->project);

        $event->delete();

        return back();
    }
}