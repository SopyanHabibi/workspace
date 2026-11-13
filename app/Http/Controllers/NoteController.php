<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function show(Note $note)
    {
        Gate::authorize('update', $note);

        $note->load('task:id,title', 'project:id,name');

        return Inertia::render('notes/Show', ['note' => $note]);
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'type' => ['required', 'in:personal,project,task'],
            'task_id' => ['nullable', 'exists:tasks,id'],
        ]);

        if (!empty($data['task_id']) && !$project->tasks()->whereKey($data['task_id'])->exists()) {
            abort(422, 'Task tidak ditemukan di proyek ini.');
        }

        $project->notes()->create($data);

        return back();
    }

    public function update(Request $request, Note $note)
    {
        Gate::authorize('update', $note);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'type' => ['sometimes', 'in:personal,project,task'],
            'task_id' => ['nullable', 'exists:tasks,id'],
        ]);

        $note->update($data);

        return back();
    }

    public function pin(Note $note)
    {
        Gate::authorize('update', $note);

        $note->update(['is_pinned' => !$note->is_pinned]);

        return back();
    }

    public function destroy(Note $note)
{
    Gate::authorize('delete', $note);

    $note->delete();

    return redirect()->route('notes.index');
}
}