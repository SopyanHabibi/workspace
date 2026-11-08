<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { FolderKanban, NotebookPen, Plus, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface NoteItem {
    id: number;
    title: string;
    content: string | null;
    type: 'personal' | 'project' | 'task';
    project_name: string;
    task_title: string | null;
    updated_at: string;
}

const props = defineProps<{
    notes: NoteItem[];
    typeFilter: string | null;
}>();

const search = ref('');
const showForm = ref(false);

const form = useForm({
    title: '',
    content: '',
    type: 'personal' as 'personal' | 'project' | 'task',
    project_id: null as number | null,
    task_id: null as number | null,
});

const tabs = [
    { label: 'All Notes', value: null },
    { label: 'Personal', value: 'personal' },
    { label: 'Project Notes', value: 'project' },
    { label: 'Task Notes', value: 'task' },
];

const typeStyle: Record<string, string> = {
    personal: 'bg-purple-500/15 text-purple-400',
    project: 'bg-primary/15 text-primary',
    task: 'bg-orange-500/15 text-orange-400',
};

const typeLabel: Record<string, string> = {
    personal: 'Personal',
    project: 'Project',
    task: 'Task',
};

const filtered = computed(() =>
    props.notes.filter(
        (n) =>
            n.title.toLowerCase().includes(search.value.toLowerCase()) ||
            (n.content ?? '').toLowerCase().includes(search.value.toLowerCase()),
    ),
);

function switchTab(value: string | null) {
    router.get('/notes', value ? { type: value } : {}, { preserveState: true });
}

function formatUpdated(value: string): string {
    const d = new Date(value);
    const now = new Date();
    const diffMs = now.getTime() - d.getTime();
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);

    if (diffHours < 1) return 'Just now';
    if (diffHours < 24) return `Updated ${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays === 1) return 'Updated yesterday';
    return `Updated ${d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}`;
}

function submit() {
    form.post('/notes', {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}
</script>

<template>
    <Head title="Notes" />

    <div class="mx-auto w-full max-w-5xl space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Notes</h1>
                <p class="text-sm text-muted-foreground">Capture ideas, thoughts, and project notes.</p>
            </div>
            <button
                @click="showForm = !showForm"
                class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                <Plus class="h-4 w-4" />
                New Note
            </button>
        </div>

        <!-- Form -->
        <div v-if="showForm" class="rounded-xl border bg-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">New Note</h2>
                <button @click="showForm = false" class="text-muted-foreground hover:text-foreground">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">Title *</label>
                    <input
                        v-model="form.title"
                        placeholder="Note title"
                        class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                    <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">Type</label>
                    <select
                        v-model="form.type"
                        class="w-full rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="personal">Personal</option>
                        <option value="project">Project Note</option>
                        <option value="task">Task Note</option>
                    </select>
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs text-muted-foreground">Content</label>
                <textarea
                    v-model="form.content"
                    placeholder="Write your note here..."
                    rows="4"
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
                    Save Note
                </button>
            </div>
        </div>

        <!-- Search -->
        <div class="relative">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <input
                v-model="search"
                placeholder="Search notes..."
                class="w-full rounded-lg border bg-card py-2 pl-9 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2">
            <button
                v-for="tab in tabs"
                :key="tab.label"
                @click="switchTab(tab.value)"
                class="rounded-full px-4 py-1.5 text-sm transition-colors border"
                :class="typeFilter === tab.value
                    ? 'bg-primary text-primary-foreground border-primary font-medium'
                    : 'text-muted-foreground hover:text-foreground'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Empty state -->
        <p v-if="filtered.length === 0" class="text-sm text-muted-foreground">
            No notes found.
        </p>

        <!-- Grid -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div
                v-for="n in filtered"
                :key="n.id"
                class="rounded-xl border bg-card p-5 space-y-3 hover:border-primary/50 transition-colors cursor-pointer"
            >
                <!-- Type badge -->
                <span
                    class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold tracking-wide uppercase"
                    :class="typeStyle[n.type]"
                >
                    {{ typeLabel[n.type] }}
                </span>

                <!-- Title & content -->
                <div>
                    <h3 class="font-semibold">{{ n.title }}</h3>
                    <p v-if="n.content" class="mt-1 line-clamp-2 text-sm text-muted-foreground">
                        {{ n.content }}
                    </p>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between pt-1 border-t border-border">
                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <FolderKanban v-if="n.type === 'project'" class="h-3.5 w-3.5" />
                        <NotebookPen v-else-if="n.type === 'task'" class="h-3.5 w-3.5" />
                        <span>
                            {{ n.type === 'task' && n.task_title ? n.task_title : n.project_name }}
                        </span>
                    </div>
                    <span class="text-xs text-muted-foreground">
                        {{ formatUpdated(n.updated_at) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>