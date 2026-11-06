<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'task_id' => ['nullable', 'exists:tasks,id'],
        ]);

        // Pastikan task yang dipilih memang milik project ini
        if (! empty($data['task_id']) && ! $project->tasks()->whereKey($data['task_id'])->exists()) {
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
            'task_id' => ['nullable', 'exists:tasks,id'],
        ]);

        if (! empty($data['task_id']) && ! $note->project->tasks()->whereKey($data['task_id'])->exists()) {
            abort(422, 'Task tidak ditemukan di proyek ini.');
        }

        $note->update($data);

        return back();
    }

    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);

        $note->delete();

        return back();
    }
}