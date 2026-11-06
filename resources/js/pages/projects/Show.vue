<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Task {
    id: number;
    title: string;
    status: 'todo' | 'in_progress' | 'done';
    importance: number;
    due_at: string | null;
}

interface Project {
    id: number;
    name: string;
    description: string | null;
    status: string;
    due_date: string | null;
    tasks: Task[];
    notes: Note[];
}

interface Note {
    id: number;
    title: string;
    content: string | null;
    task_id: number | null;
    created_at: string;
}

const props = defineProps<{ project: Project }>();

const breadcrumbs = computed(() => [
    { title: 'Projects', href: '/projects' },
    { title: props.project.name, href: `/projects/${props.project.id}` },
]);

const importanceLabel: Record<number, string> = { 1: 'Rendah', 2: 'Sedang', 3: 'Tinggi' };
const statusLabel: Record<Task['status'], string> = { todo: 'To do', in_progress: 'Dikerjakan', done: 'Selesai' };

const form = useForm({ title: '', importance: 2, due_at: '' });

const noteForm = useForm({ title: '', content: '', task_id: null as number | null });

function addNote() {
    noteForm.post(`/projects/${props.project.id}/notes`, {
        preserveScroll: true,
        onSuccess: () => noteForm.reset(),
    });
}

function removeNote(note: Note) {
    if (confirm(`Hapus catatan "${note.title}"?`)) {
        router.delete(`/notes/${note.id}`, { preserveScroll: true });
    }
}

function taskTitle(taskId: number | null): string | null {
    return props.project.tasks.find((t) => t.id === taskId)?.title ?? null;
}

function addTask() {
    form.post(`/projects/${props.project.id}/tasks`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function setStatus(task: Task, status: Task['status']) {
    router.patch(`/tasks/${task.id}`, { status }, { preserveScroll: true });
}

function removeTask(task: Task) {
    if (confirm(`Hapus tugas "${task.title}"?`)) {
        router.delete(`/tasks/${task.id}`, { preserveScroll: true });
    }
}

function removeProject() {
    if (confirm(`Hapus proyek "${props.project.name}" beserta semua tugasnya?`)) {
        router.delete(`/projects/${props.project.id}`);
    }
}

function formatDue(value: string | null): string {
    return value ? new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : 'Tanpa deadline';
}
</script>

<template>
    <Head :title="project.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">{{ project.name }}</h1>
                    <p v-if="project.description" class="text-sm text-gray-500">{{ project.description }}</p>
                </div>
                <button @click="removeProject" class="text-sm text-red-500 hover:underline">Hapus proyek</button>
            </div>

            <form @submit.prevent="addTask" class="space-y-3 rounded-lg border p-4">
                <h2 class="font-semibold">Tugas baru</h2>

                <div>
                    <input v-model="form.title" placeholder="Judul tugas" class="w-full rounded border bg-transparent px-3 py-2" />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <select v-model.number="form.importance" class="rounded border bg-transparent px-3 py-2">
                        <option :value="1" class="text-black">Penting: Rendah</option>
                        <option :value="2" class="text-black">Penting: Sedang</option>
                        <option :value="3" class="text-black">Penting: Tinggi</option>
                    </select>
                    <input v-model="form.due_at" type="datetime-local" class="rounded border bg-transparent px-3 py-2" />
                    <button :disabled="form.processing" class="rounded bg-black px-4 py-2 text-white disabled:opacity-50 dark:bg-white dark:text-black">
                        Tambah
                    </button>
                </div>
            </form>

            <p v-if="project.tasks.length === 0" class="text-sm text-gray-500">Belum ada tugas.</p>

            <ul class="space-y-2">
                <li v-for="t in project.tasks" :key="t.id" class="flex items-center justify-between gap-3 rounded-lg border p-3">
                    <div class="min-w-0">
                        <p class="truncate font-medium" :class="{ 'text-gray-400 line-through': t.status === 'done' }">{{ t.title }}</p>
                        <p class="text-xs text-gray-500">{{ importanceLabel[t.importance] }} · {{ formatDue(t.due_at) }}</p>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <select :value="t.status" @change="setStatus(t, ($event.target as HTMLSelectElement).value as Task['status'])" class="rounded border bg-transparent px-2 py-1 text-sm">
                            <option v-for="(label, value) in statusLabel" :key="value" :value="value" class="text-black">{{ label }}</option>
                        </select>
                        <button @click="removeTask(t)" class="text-sm text-red-500 hover:underline">Hapus</button>
                    </div>
                </li>
            </ul>
            <div class="space-y-3 border-t pt-6">
    <h2 class="font-semibold">Catatan</h2>

    <form @submit.prevent="addNote" class="space-y-3 rounded-lg border p-4">
        <input v-model="noteForm.title" placeholder="Judul catatan" class="w-full rounded border bg-transparent px-3 py-2" />
        <p v-if="noteForm.errors.title" class="text-sm text-red-500">{{ noteForm.errors.title }}</p>

        <textarea v-model="noteForm.content" placeholder="Isi catatan (opsional)" rows="3" class="w-full rounded border bg-transparent px-3 py-2" />

        <div class="flex items-center gap-3">
            <select v-model="noteForm.task_id" class="rounded border bg-transparent px-3 py-2">
                <option :value="null" class="text-black">Tidak terkait tugas</option>
                <option v-for="t in project.tasks" :key="t.id" :value="t.id" class="text-black">{{ t.title }}</option>
            </select>
            <button :disabled="noteForm.processing" class="rounded bg-black px-4 py-2 text-white disabled:opacity-50 dark:bg-white dark:text-black">
                Simpan
            </button>
        </div>
    </form>

    <p v-if="project.notes.length === 0" class="text-sm text-gray-500">Belum ada catatan.</p>

    <div v-for="n in project.notes" :key="n.id" class="space-y-1 rounded-lg border p-3">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-medium">{{ n.title }}</h3>
            <button @click="removeNote(n)" class="text-sm text-red-500 hover:underline">Hapus</button>
        </div>
        <p v-if="n.content" class="whitespace-pre-line text-sm text-gray-500">{{ n.content }}</p>
        <p v-if="taskTitle(n.task_id)" class="text-xs text-gray-400">Terkait tugas: {{ taskTitle(n.task_id) }}</p>
    </div>
</div>
        </div>
    </AppLayout>
</template>