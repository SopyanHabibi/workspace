<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = $request->string('q')->toString();

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $workspace = $request->user()->defaultWorkspace();
        $results = collect();

        // Projects
        $projects = $workspace->projects()
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'description', 'status']);

        $results = $results->merge($projects->map(fn ($p) => [
            'id'       => $p->id,
            'type'     => 'project',
            'title'    => $p->name,
            'subtitle' => $p->description ?? $p->status,
            'url'      => "/projects/{$p->id}",
        ]));

        // Tasks
        $tasks = $workspace->projects()
            ->with(['tasks' => fn ($q) => $q
                ->where(function ($q2) use ($query) {
                    $q2->where('title', 'like', "%{$query}%")
                       ->orWhere('description', 'like', "%{$query}%");
                })
                ->limit(5)
            ])
            ->get()
            ->pluck('tasks', 'name')
            ->map(fn ($tasks, $projectName) => $tasks->map(fn ($t) => [
                'id'       => $t->id,
                'type'     => 'task',
                'title'    => $t->title,
                'subtitle' => $projectName,
                'url'      => "/projects/{$t->project_id}",
            ]))
            ->flatten(1)
            ->take(5);

        $results = $results->merge($tasks);

        // Notes
        $notes = $workspace->projects()
            ->with(['notes' => fn ($q) => $q
                ->where(function ($q2) use ($query) {
                    $q2->where('title', 'like', "%{$query}%")
                       ->orWhere('content', 'like', "%{$query}%");
                })
                ->limit(5)
            ])
            ->get()
            ->pluck('notes', 'name')
            ->map(fn ($notes, $projectName) => $notes->map(fn ($n) => [
                'id'       => $n->id,
                'type'     => 'note',
                'title'    => $n->title,
                'subtitle' => $projectName,
                'url'      => "/notes/{$n->id}",
            ]))
            ->flatten(1)
            ->take(5);

        $results = $results->merge($notes);

        // Documents
        $documents = $workspace->projects()
            ->with(['documents' => fn ($q) => $q
                ->where('original_name', 'like', "%{$query}%")
                ->limit(5)
            ])
            ->get()
            ->pluck('documents', 'name')
            ->map(fn ($docs, $projectName) => $docs->map(fn ($d) => [
                'id'       => $d->id,
                'type'     => 'document',
                'title'    => $d->original_name,
                'subtitle' => $projectName,
                'url'      => "/documents/{$d->id}/preview",
            ]))
            ->flatten(1)
            ->take(5);

        $results = $results->merge($documents);

        return response()->json($results->values());
    }
}