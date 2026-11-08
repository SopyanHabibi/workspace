<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Circle,
    Clock,
    FolderKanban,
    MoreHorizontal,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Task {
    id: number;
    title: string;
    status: 'todo' | 'in_progress' | 'done';
    importance: number;
    due_at: string | null;
}

interface Note {
    id: number;
    title: string;
    content: string | null;
    task_id: number | null;
    created_at: string;
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

const props = defineProps<{ project: Project }>();

const activeTab = ref<'tasks' | 'notes'>('tasks');

const importanceLabel: Record<number, string> = { 1: 'Low', 2: 'Medium', 3: 'High' };
const importanceStyle: Record<number, string> = {
    1: 'bg-green-500/15 text-green-400',
    2: 'bg-yellow-500/15 text-yellow-400',
    3: 'bg-red-500/15 text-red-400',
};

const statusIcon: Record<Task['status'], any> = {
    todo: Circle,
    in_progress: Clock,
    done: CheckCircle2,
};

const statusStyle: Record<Task['status'], string> = {
    todo: 'text-muted-foreground',
    in_progress: 'text-primary',
    done: 'text-green-500',
};

const taskForm = useForm({
    title: '',
    importance: 2,
    due_at: '',
});

const noteForm = useForm({
    title: '',
    content: '',
    task_id: null as number | null,
});

const totalTasks = computed(() => props.project.tasks.length);
const doneTasks = computed(() => props.project.tasks.filter((t) => t.status === 'done').length);
const progress = computed(() =>
    totalTasks.value ? Math.round((doneTasks.value / totalTasks.value) * 100) : 0,
);

function addTask() {
    taskForm.post(`/projects/${props.project.id}/tasks`, {
        preserveScroll: true,
        onSuccess: () => taskForm.reset(),
    });
}

function setStatus(task: Task, status: Task['status']) {
    router.patch(`/tasks/${task.id}`, { status }, { preserveScroll: true });
}

function removeTask(task: Task) {
    if (confirm(`Delete task "${task.title}"?`)) {
        router.delete(`/tasks/${task.id}`, { preserveScroll: true });
    }
}

function addNote() {
    noteForm.post(`/projects/${props.project.id}/notes`, {
        preserveScroll: true,
        onSuccess: () => noteForm.reset(),
    });
}

function removeNote(note: Note) {
    if (confirm(`Delete note "${note.title}"?`)) {
        router.delete(`/notes/${note.id}`, { preserveScroll: true });
    }
}

function removeProject() {
    if (confirm(`Delete project "${props.project.name}" and all its data?`)) {
        router.delete(`/projects/${props.project.id}`);
    }
}

function formatDue(value: string | null): string {
    if (!value) return 'No deadline';
    const d = new Date(value);
    const now = new Date();
    const diffDays = Math.ceil((d.getTime() - now.getTime()) / (1000 * 60 * 60 * 24));
    if (diffDays < 0) return 'Overdue';
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Tomorrow';
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function taskTitle(taskId: number | null): string | null {
    return props.project.tasks.find((t) => t.id === taskId)?.title ?? null;
}
</script>

<template>
    <Head :title="project.name" />

    <div class="mx-auto w-full max-w-5xl space-y-6 p-6">
        <!-- Back + actions -->
        <div class="flex items-center justify-between">
            <Link
                href="/projects"
                class="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to Projects
            </Link>
            <button
                @click="removeProject"
                class="flex items-center gap-1.5 rounded-lg border border-red-500/30 px-3 py-1.5 text-xs text-red-400 hover:bg-red-500/10 transition-colors"
            >
                <Trash2 class="h-3.5 w-3.5" />
                Delete Project
            </button>
        </div>

        <!-- Project header -->
        <div class="rounded-xl border bg-card p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/15">
                        <FolderKanban class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">{{ project.name }}</h1>
                        <p v-if="project.description" class="text-sm text-muted-foreground">
                            {{ project.description }}
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-full px-3 py-1 text-xs font-medium shrink-0"
                    :class="project.status === 'completed'
                        ? 'bg-green-500/15 text-green-400'
                        : project.status === 'archived'
                            ? 'bg-muted text-muted-foreground'
                            : 'bg-primary/15 text-primary'"
                >
                    {{ project.status === 'active' ? 'In Progress' : project.status.charAt(0).toUpperCase() + project.status.slice(1) }}
                </span>
            </div>

            <!-- Progress -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Progress</span>
                    <span class="font-medium">{{ doneTasks }}/{{ totalTasks }} tasks ({{ progress }}%)</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full rounded-full bg-primary transition-all duration-500"
                        :style="{ width: progress + '%' }"
                    />
                </div>
            </div>

            <!-- Meta -->
            <div class="flex items-center gap-4 text-xs text-muted-foreground">
                <div class="flex items-center gap-1">
                    <CalendarDays class="h-3.5 w-3.5" />
                    <span>Due: {{ formatDue(project.due_date) }}</span>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 rounded-lg border bg-card p-1 w-fit">
            <button
                @click="activeTab = 'tasks'"
                class="rounded-md px-4 py-1.5 text-sm transition-colors"
                :class="activeTab === 'tasks'
                    ? 'bg-primary text-primary-foreground font-medium'
                    : 'text-muted-foreground hover:text-foreground'"
            >
                Tasks ({{ totalTasks }})
            </button>
            <button
                @click="activeTab = 'notes'"
                class="rounded-md px-4 py-1.5 text-sm transition-colors"
                :class="activeTab === 'notes'
                    ? 'bg-primary text-primary-foreground font-medium'
                    : 'text-muted-foreground hover:text-foreground'"
            >
                Notes ({{ project.notes.length }})
            </button>
        </div>

        <!-- Tasks tab -->
        <div v-if="activeTab === 'tasks'" class="space-y-3">
            <!-- Add task form -->
            <div class="rounded-xl border bg-card p-4 space-y-3">
                <h2 class="text-sm font-semibold">Add Task</h2>
                <div class="flex flex-wrap gap-3">
                    <input
                        v-model="taskForm.title"
                        placeholder="Task title"
                        class="flex-1 min-w-48 rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        @keydown.enter="addTask"
                    />
                    <select
                        v-model.number="taskForm.importance"
                        class="rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option :value="1">Low</option>
                        <option :value="2">Medium</option>
                        <option :value="3">High</option>
                    </select>
                    <input
                        v-model="taskForm.due_at"
                        type="datetime-local"
                        class="rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                    <button
                        @click="addTask"
                        :disabled="taskForm.processing"
                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        <Plus class="h-4 w-4" />
                        Add
                    </button>
                </div>
                <p v-if="taskForm.errors.title" class="text-xs text-red-500">{{ taskForm.errors.title }}</p>
            </div>

            <!-- Task list -->
            <p v-if="project.tasks.length === 0" class="text-sm text-muted-foreground">
                No tasks yet. Add one above.
            </p>

            <div
                v-for="t in project.tasks"
                :key="t.id"
                class="flex items-center gap-3 rounded-xl border bg-card p-4 hover:border-primary/30 transition-colors"
            >
                <!-- Status toggle -->
                <button
                    @click="setStatus(t, t.status === 'done' ? 'todo' : t.status === 'todo' ? 'in_progress' : 'done')"
                    class="shrink-0 transition-colors"
                    :class="statusStyle[t.status]"
                >
                    <component :is="statusIcon[t.status]" class="h-5 w-5" />
                </button>

                <!-- Title & meta -->
                <div class="flex-1 min-w-0">
                    <p
                        class="text-sm font-medium truncate"
                        :class="{ 'line-through text-muted-foreground': t.status === 'done' }"
                    >
                        {{ t.title }}
                    </p>
                    <p class="text-xs text-muted-foreground">{{ formatDue(t.due_at) }}</p>
                </div>

                <!-- Importance badge -->
                <span
                    class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="importanceStyle[t.importance]"
                >
                    {{ importanceLabel[t.importance] }}
                </span>

                <!-- Status select -->
                <select
                    :value="t.status"
                    @change="setStatus(t, ($event.target as HTMLSelectElement).value as Task['status'])"
                    class="shrink-0 rounded-lg border bg-card px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-primary"
                >
                    <option value="todo">To do</option>
                    <option value="in_progress">In Progress</option>
                    <option value="done">Done</option>
                </select>

                <!-- Delete -->
                <button
                    @click="removeTask(t)"
                    class="shrink-0 text-muted-foreground hover:text-red-400 transition-colors"
                >
                    <Trash2 class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Notes tab -->
        <div v-if="activeTab === 'notes'" class="space-y-3">
            <!-- Add note form -->
            <div class="rounded-xl border bg-card p-4 space-y-3">
                <h2 class="text-sm font-semibold">Add Note</h2>
                <input
                    v-model="noteForm.title"
                    placeholder="Note title"
                    class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <textarea
                    v-model="noteForm.content"
                    placeholder="Note content (optional)"
                    rows="3"
                    class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <div class="flex items-center justify-between gap-3">
                    <select
                        v-model="noteForm.task_id"
                        class="rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option :value="null">Not linked to a task</option>
                        <option v-for="t in project.tasks" :key="t.id" :value="t.id">
                            {{ t.title }}
                        </option>
                    </select>
                    <button
                        @click="addNote"
                        :disabled="noteForm.processing"
                        class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                    >
                        <Plus class="h-4 w-4" />
                        Add Note
                    </button>
                </div>
            </div>

            <!-- Note list -->
            <p v-if="project.notes.length === 0" class="text-sm text-muted-foreground">
                No notes yet.
            </p>

            <div
                v-for="n in project.notes"
                :key="n.id"
                class="rounded-xl border bg-card p-4 space-y-2 hover:border-primary/30 transition-colors"
            >
                <div class="flex items-start justify-between gap-2">
                    <h3 class="font-medium text-sm">{{ n.title }}</h3>
                    <button
                        @click="removeNote(n)"
                        class="shrink-0 text-muted-foreground hover:text-red-400 transition-colors"
                    >
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>
                <p v-if="n.content" class="text-sm text-muted-foreground whitespace-pre-line">
                    {{ n.content }}
                </p>
                <p v-if="taskTitle(n.task_id)" class="text-xs text-primary">
                    Linked to: {{ taskTitle(n.task_id) }}
                </p>
            </div>
        </div>
    </div>
</template>