<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteListController extends Controller
{
    public function index(Request $request)
    {
        $typeFilter = $request->string('type')->toString() ?: null;
        $workspace = $request->user()->defaultWorkspace();

        $projects = $workspace->projects()
            ->with('tasks:id,title,project_id')
            ->get(['id', 'name']);

        $notes = $workspace->projects()
            ->with(['notes' => function ($q) use ($typeFilter) {
                if ($typeFilter) {
                    $q->where('type', $typeFilter);
                }
                $q->with('task:id,title')->latest();
            }, 'notes.task'])
            ->get()
            ->pluck('notes', 'name')
            ->map(fn ($notes, $projectName) => $notes->map(fn ($note) => [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'type' => $note->type,
                'is_pinned' => $note->is_pinned,
                'project_name' => $projectName,
                'task_title' => $note->task?->title,
                'updated_at' => $note->updated_at,
            ]))
            ->flatten(1)
            ->sort(fn ($a, $b) =>
                $b['is_pinned'] <=> $a['is_pinned'] ?: $b['updated_at'] <=> $a['updated_at']
            )
            ->values();

        return Inertia::render('notes/Index', [
            'notes' => $notes,
            'typeFilter' => $typeFilter,
            'projects' => $projects,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'type' => ['required', 'in:personal,project,task'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'task_id' => ['nullable', 'exists:tasks,id'],
        ]);

        $workspace = $request->user()->defaultWorkspace();

        // Semua tipe butuh project — personal pakai project "Personal" otomatis
        if ($data['type'] === 'personal' || empty($data['project_id'])) {
            $data['project_id'] = $workspace->projects()
                ->firstOrCreate(
                    ['name' => 'Personal'],
                    ['description' => 'Personal notes']
                )->id;
        }

        $workspace->projects()
            ->findOrFail($data['project_id'])
            ->notes()
            ->create($data);

        return back();
    }
}