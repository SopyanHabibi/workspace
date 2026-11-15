<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->toString() ?: null;
        $category = $request->string('category')->toString() ?: null;

        $workspace = $request->user()->defaultWorkspace();

        $documents = $workspace->projects()
            ->with(['documents' => function ($q) use ($search, $category) {
                if ($search) {
                    $q->where('original_name', 'like', "%{$search}%");
                }
                if ($category) {
                    $q->where('category', $category);
                }
                $q->with('task:id,title')->latest();
            }])
            ->get()
            ->pluck('documents', 'name')
            ->map(fn ($docs, $projectName) => $docs->map(fn ($d) => [
                'id'             => $d->id,
                'original_name'  => $d->original_name,
                'category'       => $d->category,
                'size_formatted' => $d->size_formatted,
                'mime_type'      => $d->mime_type,
                'download_url'   => $d->download_url,
                'project_name'   => $projectName,
                'project_id'     => $d->project_id,
                'task_title'     => $d->task?->title,
                'created_at'     => $d->created_at,
            ]))
            ->flatten(1)
            ->values();

        $projects = $workspace->projects()->get(['id', 'name']);

        $stats = [
            'total'        => $documents->count(),
            'total_size'   => $this->formatSize($workspace->projects()
                ->with('documents:id,project_id,size')
                ->get()
                ->pluck('documents')
                ->flatten()
                ->sum('size')),
        ];

        return Inertia::render('documents/Index', [
            'documents' => $documents,
            'projects'  => $projects,
            'search'    => $search,
            'category'  => $category,
            'stats'     => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'       => ['required', 'file', 'max:20480'], // 20MB
            'project_id' => ['required', 'exists:projects,id'],
            'task_id'    => ['nullable', 'exists:tasks,id'],
            'category'   => ['required', 'in:report,presentation,image,spreadsheet,other'],
        ]);

        $project = Project::findOrFail($request->project_id);
        Gate::authorize('update', $project);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('documents', 'local');

        $project->documents()->create([
            'name'          => Str::uuid(),
            'original_name' => $originalName,
            'path'          => $path,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'category'      => $request->category,
            'task_id'       => $request->task_id,
        ]);

        return back();
    }

    public function download(Document $document)
    {
        Gate::authorize('view', $document);

        return Storage::disk('local')->download(
            $document->path,
            $document->original_name
        );
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back();
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024) return "{$bytes} B";
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}