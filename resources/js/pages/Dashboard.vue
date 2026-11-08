<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronRight,
    CircleDot,
    FolderKanban,
    Plus,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface ProjectSummary {
    id: number;
    name: string;
    status: string;
    tasks_count: number;
    done_tasks_count: number;
}

interface UpcomingTask {
    id: number;
    title: string;
    project_id: number;
    project_name: string;
    importance: number;
    due_at: string | null;
    priority_level: string;
}

interface ActivityLog {
    action: string;
    description: string | null;
    created_at: string;
}

const props = defineProps<{
    stats: { total_projects: number; total_tasks: number; done_tasks: number };
    projects: ProjectSummary[];
    upcomingTasks: UpcomingTask[];
    recentActivity: ActivityLog[];
}>();

const user = (window as any).__inertia_page?.props?.auth?.user;

const inProgressTasks = computed(() =>
    props.upcomingTasks.filter((t) => t.priority_level !== 'done').slice(0, 3),
);

const urgentTasks = computed(() =>
    props.upcomingTasks
        .filter((t) => ['overdue', 'urgent'].includes(t.priority_level))
        .slice(0, 3),
);

function progress(p: ProjectSummary): number {
    return p.tasks_count
        ? Math.round((p.done_tasks_count / p.tasks_count) * 100)
        : 0;
}

function formatDue(value: string | null): string {
    if (!value) return 'No deadline';
    const d = new Date(value);
    const now = new Date();
    const diffDays = Math.ceil(
        (d.getTime() - now.getTime()) / (1000 * 60 * 60 * 24),
    );
    if (diffDays < 0) return 'Overdue';
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

const levelStyle: Record<string, string> = {
    overdue: 'bg-red-500/15 text-red-400',
    urgent: 'bg-orange-500/15 text-orange-400',
    soon: 'bg-yellow-500/15 text-yellow-400',
    normal: 'bg-muted text-muted-foreground',
    done: 'bg-green-500/15 text-green-400',
};

const levelLabel: Record<string, string> = {
    overdue: 'Overdue',
    urgent: 'Urgent',
    soon: 'Soon',
    normal: 'Normal',
    done: 'Done',
};
</script>

<template>
    <Head title="Dashboard" />

        <div class="mx-auto w-full max-w-6xl space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">
                        Hi, {{ $page.props.auth?.user?.name?.split(' ')[0] ?? 'there' }}! 👋
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Here's what's going on with your workspace today.
                    </p>
                </div>
                <Link
                    href="/projects"
                    class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    New Project
                </Link>
            </div>

            <!-- Stats row -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Total Projects</p>
                        <FolderKanban class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ stats.total_projects }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">Active workspaces</p>
                </div>

                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">In Progress</p>
                        <CircleDot class="h-4 w-4 text-primary" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">
                        {{ stats.total_tasks - stats.done_tasks }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">Tasks remaining</p>
                </div>

                <div class="rounded-xl border bg-card p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">Completed</p>
                        <CheckCircle2 class="h-4 w-4 text-green-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold text-green-500">
                        {{ stats.done_tasks }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        of {{ stats.total_tasks }} total tasks
                    </p>
                </div>
            </div>

            <!-- Middle row -->
            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Tasks in progress -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold">Tasks In Progress</h2>
                        <Link
                            href="/tasks"
                            class="flex items-center gap-1 text-xs text-primary hover:underline"
                        >
                            See all <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <p v-if="inProgressTasks.length === 0" class="text-sm text-muted-foreground">
                        No tasks in progress. 🎉
                    </p>

                    <div class="space-y-3">
                        <Link
                            v-for="t in inProgressTasks"
                            :key="t.id"
                            :href="`/projects/${t.project_id}`"
                            class="flex items-center justify-between rounded-lg border p-3 hover:bg-accent"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ t.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ t.project_name }} · {{ formatDue(t.due_at) }}
                                </p>
                            </div>
                            <span
                                class="ml-3 shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="levelStyle[t.priority_level]"
                            >
                                {{ levelLabel[t.priority_level] }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Urgent tasks -->
                <div class="rounded-xl border bg-card p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold">Needs Attention</h2>
                        <Link
                            href="/tasks?status=todo"
                            class="flex items-center gap-1 text-xs text-primary hover:underline"
                        >
                            See all <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>

                    <p v-if="urgentTasks.length === 0" class="text-sm text-muted-foreground">
                        Nothing urgent right now. 🙌
                    </p>

                    <div class="space-y-3">
                        <Link
                            v-for="t in urgentTasks"
                            :key="t.id"
                            :href="`/projects/${t.project_id}`"
                            class="flex items-center justify-between rounded-lg border p-3 hover:bg-accent"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ t.title }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ t.project_name }} · {{ formatDue(t.due_at) }}
                                </p>
                            </div>
                            <span
                                class="ml-3 shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="levelStyle[t.priority_level]"
                            >
                                {{ levelLabel[t.priority_level] }}
                            </span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Last Projects -->
            <div class="rounded-xl border bg-card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold">Last Projects</h2>
                    <Link
                        href="/projects"
                        class="flex items-center gap-1 text-xs text-primary hover:underline"
                    >
                        See all <ChevronRight class="h-3 w-3" />
                    </Link>
                </div>

                <p v-if="projects.length === 0" class="text-sm text-muted-foreground">
                    No projects yet.
                </p>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="p in projects.slice(0, 6)"
                        :key="p.id"
                        :href="`/projects/${p.id}`"
                        class="space-y-3 rounded-lg border p-4 hover:bg-accent"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary/15">
                                <FolderKanban class="h-4 w-4 text-primary" />
                            </div>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs"
                                :class="p.status === 'completed'
                                    ? 'bg-green-500/15 text-green-400'
                                    : p.status === 'archived'
                                        ? 'bg-muted text-muted-foreground'
                                        : 'bg-primary/15 text-primary'"
                            >
                                {{ p.status }}
                            </span>
                        </div>

                        <div>
                            <p class="font-medium">{{ p.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ p.done_tasks_count }}/{{ p.tasks_count }} tasks done
                            </p>
                        </div>

                        <div class="space-y-1">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-primary transition-all"
                                    :style="{ width: progress(p) + '%' }"
                                />
                            </div>
                            <p class="text-right text-xs text-muted-foreground">
                                {{ progress(p) }}%
                            </p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="rounded-xl border bg-card p-5">
                <h2 class="mb-4 font-semibold">Recent Activity</h2>

                <p v-if="recentActivity.length === 0" class="text-sm text-muted-foreground">
                    No activity recorded yet.
                </p>

                <ul class="space-y-2">
                    <li
                        v-for="(log, i) in recentActivity"
                        :key="i"
                        class="flex items-center gap-3 text-sm"
                    >
                        <div class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                        <span class="flex-1 text-muted-foreground">
                            {{ log.description ?? log.action }}
                        </span>
                        <span class="shrink-0 text-xs text-muted-foreground">
                            {{ new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
</template>