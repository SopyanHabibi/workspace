<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'name' => config('app.name'),
        'auth' => [
            'user' => $request->user(),
        ],
        'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        'notifications' => function () use ($request) {
            if (! $request->user()) return [];

            $workspace = $request->user()->defaultWorkspace();

            return $workspace->projects()
                ->with(['tasks' => fn ($q) => $q
                    ->whereNotNull('due_at')
                    ->where('status', '!=', 'done')
                    ->where('due_at', '<=', now()->addDays(2))
                    ->orderBy('due_at')
                ])
                ->get()
                ->pluck('tasks', 'name')
                ->map(fn ($tasks, $projectName) => $tasks->map(fn ($t) => [
                    'id'           => $t->id,
                    'title'        => $t->title,
                    'project_name' => $projectName,
                    'due_at'       => $t->due_at,
                    'is_overdue'   => $t->due_at->isPast(),
                    'is_today'     => $t->due_at->isToday(),
                    'is_tomorrow'  => $t->due_at->isTomorrow(),
                ]))
                ->flatten(1)
                ->values();
        },
    ];
}
}
