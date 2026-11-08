<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    FolderKanban,
    Plus,
    Search,
    SlidersHorizontal,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Project {
    id: number;
    name: string;
    description: string | null;
    status: string;
    due_date: string | null;
    tasks_count: number;
    done_tasks_count: number;
    in_progress_count: number;
}

const props = defineProps<{
    projects: Project[];
    statusFilter: string | null;
    stats: { total: number; in_progress: number; completed: number };
}>();

const search = ref('');
const showForm = ref(false);

const form = useForm({
    name: '',
    description: '',
    due_date: '',
});

const tabs = [
    { label: 'All Projects', value: null },
    { label: 'In Progress', value: 'active' },
    { label: 'Completed', value: 'completed' },
    { label: 'Archived', value: 'archived' },
];

const filtered = computed(() =>
    props.projects.filter((p) =>
        p.name.toLowerCase().includes(search.value.toLowerCase()) ||
        (p.description ?? '').toLowerCase().includes(search.value.toLowerCase()),
    ),
);

function switchTab(value: string | null) {
    router.get('/projects', value ? { status: value } : {}, { preserveState: true });
}

function progress(p: Project): number {
    return p.tasks_count
        ? Math.round((p.done_tasks_count / p.tasks_count) * 100)
        : 0;
}

function submit() {
    form.post('/projects', {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}
</script>

<template>
    <Head title="Projects" />

    <div class="mx-auto w-full max-w-6xl space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Projects</h1>
                <p class="text-sm text-muted-foreground">Manage and track all your projects.</p>
            </div>
            <button
                @click="showForm = !showForm"
                class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                <Plus class="h-4 w-4" />
                New Project
            </button>
        </div>

        <!-- Form tambah proyek -->
        <div v-if="showForm" class="rounded-xl border bg-card p-5 space-y-4">
            <h2 class="font-semibold">New Project</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">Name *</label>
                    <input
                        v-model="form.name"
                        placeholder="Project name"
                        class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                    <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">Due Date</label>
                    <input
                        v-model="form.due_date"
                        type="date"
                        class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
            </div>
            <div class="space-y-1">
                <label class="text-xs text-muted-foreground">Description</label>
                <textarea
                    v-model="form.description"
                    placeholder="What is this project about?"
                    rows="2"
                    class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                />
            </div>
            <div class="flex justify-end gap-2">
                <button
                    @click="showForm = false"
                    class="rounded-lg border px-4 py-2 text-sm hover:bg-accent"
                >
                    Cancel
                </button>
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                >
                    Save Project
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border bg-card p-4">
                <p class="text-sm text-muted-foreground">Total Projects</p>
                <p class="mt-1 text-3xl font-bold">{{ stats.total }}</p>
            </div>
            <div class="rounded-xl border bg-card p-4">
                <p class="text-sm text-muted-foreground">In Progress</p>
                <p class="mt-1 text-3xl font-bold text-primary">{{ stats.in_progress }}</p>
            </div>
            <div class="rounded-xl border bg-card p-4">
                <p class="text-sm text-muted-foreground">Completed</p>
                <p class="mt-1 text-3xl font-bold text-green-500">{{ stats.completed }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input
                    v-model="search"
                    placeholder="Search projects..."
                    class="w-full rounded-lg border bg-card py-2 pl-9 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                />
            </div>
            <button class="rounded-lg border bg-card p-2 hover:bg-accent">
                <SlidersHorizontal class="h-4 w-4 text-muted-foreground" />
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 rounded-lg border bg-card p-1 w-fit">
            <button
                v-for="tab in tabs"
                :key="tab.label"
                @click="switchTab(tab.value)"
                class="rounded-md px-4 py-1.5 text-sm transition-colors"
                :class="statusFilter === tab.value
                    ? 'bg-primary text-primary-foreground font-medium'
                    : 'text-muted-foreground hover:text-foreground'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Grid proyek -->
        <p v-if="filtered.length === 0" class="text-sm text-muted-foreground">
            No projects found.
        </p>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
    v-for="p in filtered"
    :key="p.id"
    :href="`/projects/${p.id}`"
    class="group relative rounded-xl border bg-card p-5 space-y-4 hover:border-primary/50 transition-colors block"
>
                <!-- Top row -->
                <div class="flex items-start justify-between">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/15">
                        <FolderKanban class="h-5 w-5 text-primary" />
                    </div>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="p.status === 'completed'
                            ? 'bg-green-500/15 text-green-400'
                            : p.status === 'archived'
                                ? 'bg-muted text-muted-foreground'
                                : 'bg-primary/15 text-primary'"
                    >
                        {{ p.status === 'active' ? 'In Progress' : p.status.charAt(0).toUpperCase() + p.status.slice(1) }}
                    </span>
                </div>

                <!-- Name & description -->
                <div>
                    <h3 class="font-semibold">{{ p.name }}</h3>
                    <p v-if="p.description" class="mt-0.5 line-clamp-2 text-xs text-muted-foreground">
                        {{ p.description }}
                    </p>
                </div>

                <!-- Progress -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs text-muted-foreground">
                        <span>Progress</span>
                        <span>{{ progress(p) }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full bg-primary transition-all"
                            :style="{ width: progress(p) + '%' }"
                        />
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted-foreground">
                        {{ p.done_tasks_count }}/{{ p.tasks_count }} tasks
                    </span>
                    <Link
                        :href="`/projects/${p.id}`"
                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-muted hover:bg-primary hover:text-primary-foreground transition-colors"
                    >
                        <ArrowUpRight class="h-4 w-4" />
                    </Link>
                </div>
            </Link>
        </div>
    </div>
</template>