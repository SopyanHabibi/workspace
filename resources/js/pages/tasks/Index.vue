<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface TaskRow {
    id: number;
    title: string;
    status: 'todo' | 'in_progress' | 'done';
    importance: number;
    due_at: string | null;
    project_id: number;
    project_name: string;
    priority_level: 'overdue' | 'urgent' | 'soon' | 'normal' | 'done';
}

defineProps<{ tasks: TaskRow[]; statusFilter: string | null }>();

const breadcrumbs = [{ title: 'Semua Tugas', href: '/tasks' }];

const levelStyle: Record<TaskRow['priority_level'], string> = {
    overdue: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    urgent: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    soon: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    normal: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
    done: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
};

const levelLabel: Record<TaskRow['priority_level'], string> = {
    overdue: 'Terlambat',
    urgent: 'Mendesak',
    soon: 'Segera',
    normal: 'Normal',
    done: 'Selesai',
};

function formatDue(value: string | null): string {
    if (!value) return 'Tanpa deadline';
    return new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

function filterBy(status: string | null) {
    router.get('/tasks', status ? { status } : {}, { preserveState: true });
}
</script>

<template>
    <Head title="Semua Tugas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl space-y-4 p-4">
            <div class="flex flex-wrap items-center gap-2">
                <button
                    v-for="opt in [null, 'todo', 'in_progress', 'done']"
                    :key="opt ?? 'all'"
                    @click="filterBy(opt)"
                    class="rounded border px-3 py-1 text-sm"
                    :class="statusFilter === opt ? 'bg-black text-white dark:bg-white dark:text-black' : ''"
                >
                    {{ opt === null ? 'Semua' : opt === 'todo' ? 'To do' : opt === 'in_progress' ? 'Dikerjakan' : 'Selesai' }}
                </button>
            </div>

            <p v-if="tasks.length === 0" class="text-sm text-gray-500">Tidak ada tugas.</p>

            <Link
                v-for="t in tasks"
                :key="t.id"
                :href="`/projects/${t.project_id}`"
                class="flex items-center justify-between gap-3 rounded-lg border p-3 hover:bg-gray-50 dark:hover:bg-gray-900"
            >
                <div class="min-w-0">
                    <p class="truncate font-medium" :class="{ 'text-gray-400 line-through': t.status === 'done' }">{{ t.title }}</p>
                    <p class="text-xs text-gray-500">{{ t.project_name }} · {{ formatDue(t.due_at) }}</p>
                </div>
                <span class="shrink-0 rounded px-2 py-0.5 text-xs font-medium" :class="levelStyle[t.priority_level]">
                    {{ levelLabel[t.priority_level] }}
                </span>
            </Link>
        </div>
    </AppLayout>
</template>