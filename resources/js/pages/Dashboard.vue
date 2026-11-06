<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';

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
}

interface ActivityLog {
    action: string;
    description: string | null;
    created_at: string;
}

defineProps<{
    stats: { total_projects: number; total_tasks: number; done_tasks: number };
    projects: ProjectSummary[];
    upcomingTasks: UpcomingTask[];
    recentActivity: ActivityLog[];
}>();

const breadcrumbs = [{ title: 'Dashboard', href: dashboard().url }];

function progress(p: ProjectSummary): number {
    return p.tasks_count ? Math.round((p.done_tasks_count / p.tasks_count) * 100) : 0;
}

function formatDue(value: string | null): string {
    if (!value) return 'Tanpa deadline';
    return new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-5xl space-y-6 p-4">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border p-4">
                    <p class="text-sm text-gray-500">Total Proyek</p>
                    <p class="text-2xl font-bold">{{ stats.total_projects }}</p>
                </div>
                <div class="rounded-lg border p-4">
                    <p class="text-sm text-gray-500">Total Tugas</p>
                    <p class="text-2xl font-bold">{{ stats.total_tasks }}</p>
                </div>
                <div class="rounded-lg border p-4">
                    <p class="text-sm text-gray-500">Tugas Selesai</p>
                    <p class="text-2xl font-bold">{{ stats.done_tasks }} / {{ stats.total_tasks }}</p>
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="font-semibold">Perlu diperhatikan</h2>
                <p v-if="upcomingTasks.length === 0" class="text-sm text-gray-500">Tidak ada tugas yang mendesak. 🎉</p>

                <Link
                    v-for="t in upcomingTasks"
                    :key="t.id"
                    :href="`/projects/${t.project_id}`"
                    class="flex items-center justify-between gap-3 rounded-lg border p-3 hover:bg-gray-50 dark:hover:bg-gray-900"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ t.title }}</p>
                        <p class="text-xs text-gray-500">{{ t.project_name }} · {{ formatDue(t.due_at) }}</p>
                    </div>
                    <span class="shrink-0 rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800">
                        Penting: {{ ['', 'Rendah', 'Sedang', 'Tinggi'][t.importance] }}
                    </span>
                </Link>
            </div>

            <div class="space-y-3">
                <h2 class="font-semibold">Progres Proyek</h2>
                <p v-if="projects.length === 0" class="text-sm text-gray-500">Belum ada proyek.</p>

                <Link
                    v-for="p in projects"
                    :key="p.id"
                    :href="`/projects/${p.id}`"
                    class="block space-y-2 rounded-lg border p-3 hover:bg-gray-50 dark:hover:bg-gray-900"
                >
                    <div class="flex items-center justify-between">
                        <p class="font-medium">{{ p.name }}</p>
                        <span class="text-xs text-gray-500">{{ p.done_tasks_count }}/{{ p.tasks_count }}</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded bg-gray-200 dark:bg-gray-800">
                        <div class="h-full bg-green-500" :style="{ width: progress(p) + '%' }" />
                    </div>
                </Link>
            </div>

            <div class="space-y-3">
                <h2 class="font-semibold">Aktivitas Terbaru</h2>
                <p v-if="recentActivity.length === 0" class="text-sm text-gray-500">Belum ada aktivitas tercatat.</p>

                <ul class="space-y-1">
                    <li v-for="(log, i) in recentActivity" :key="i" class="text-sm text-gray-500">
                        {{ log.description ?? log.action }} ·
                        <span class="text-xs">{{ new Date(log.created_at).toLocaleString('id-ID') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>