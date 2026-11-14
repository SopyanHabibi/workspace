<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus, X, Trash2} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

interface CalendarEntry {
    id: number;
    title: string;
    date: string;
    starts_at: string;
    ends_at: string | null;
    type: 'event' | 'deadline';
    project: string;
}

interface ProjectOption {
    id: number;
    name: string;
}

const props = defineProps<{
    year: number;
    month: number;
    entries: CalendarEntry[];
    projects: ProjectOption[];
}>();

const showForm = ref(false);
const selectedEntry = ref<CalendarEntry | null>(null);
    const eventToDelete = ref<CalendarEntry | null>(null);
const deleting = ref(false);

const form = useForm({
    title: '',
    project_id: null as number | null,
    starts_at: '',
    ends_at: '',
    task_id: null,
});

// Nama bulan
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December',
];

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

// Hitung grid kalender
const calendarDays = computed(() => {
    const firstDay = new Date(props.year, props.month - 1, 1).getDay();
    const daysInMonth = new Date(props.year, props.month, 0).getDate();
    const days: { date: string | null; day: number | null }[] = [];

    // Padding awal
    for (let i = 0; i < firstDay; i++) {
        days.push({ date: null, day: null });
    }

    // Hari-hari dalam bulan
    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = `${props.year}-${String(props.month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        days.push({ date: dateStr, day: d });
    }

    return days;
});

// Entry per tanggal
function entriesForDate(date: string): CalendarEntry[] {
    return props.entries.filter((e) => e.date === date);
}

function isToday(date: string): boolean {
    return date === new Date().toISOString().slice(0, 10);
}

function prevMonth() {
    const d = new Date(props.year, props.month - 2, 1);
    router.get('/calendar', { year: d.getFullYear(), month: d.getMonth() + 1 }, { preserveState: true });
}

function nextMonth() {
    const d = new Date(props.year, props.month, 1);
    router.get('/calendar', { year: d.getFullYear(), month: d.getMonth() + 1 }, { preserveState: true });
}

function goToday() {
    router.get('/calendar', {}, { preserveState: true });
}

function openForm(date?: string) {
    if (date) form.starts_at = `${date}T00:00`;
    showForm.value = true;
}

function submit() {
    form.post('/calendar', {
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function destroy(entry: CalendarEntry) {
    if (entry.type === 'deadline') return;

    eventToDelete.value = entry;
}

function cancelDelete() {
    if (deleting.value) return;

    eventToDelete.value = null;
}

function confirmDelete() {
    const event = eventToDelete.value;

    if (!event || deleting.value) return;

    deleting.value = true;

    router.delete(`/calendar/${event.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Event deleted successfully.');
            eventToDelete.value = null;
            selectedEntry.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
}

function formatTime(value: string): string {
    return new Date(value).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Calendar" />

    <div class="mx-auto w-full max-w-6xl space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Calendar</h1>
                <p class="text-sm text-muted-foreground">Track events and task deadlines.</p>
            </div>
            <button
                @click="openForm()"
                class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                <Plus class="h-4 w-4" />
                New Event
            </button>
        </div>

        <!-- Form tambah event -->
        <div v-if="showForm" class="rounded-xl border bg-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">New Event</h2>
                <button @click="showForm = false" class="text-muted-foreground hover:text-foreground">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-xs text-muted-foreground">Title *</label>
                    <input
                        v-model="form.title"
                        placeholder="Event title"
                        class="w-full rounded-lg border bg-transparent px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                    <p v-if="form.errors.title" class="text-xs text-red-500">{{ form.errors.title }}</p>
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">Project *</label>
                    <select
                        v-model="form.project_id"
                        class="w-full rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option :value="null">Select project...</option>
                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">Start *</label>
                    <input
                        v-model="form.starts_at"
                        type="datetime-local"
                        class="w-full rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-muted-foreground">End</label>
                    <input
                        v-model="form.ends_at"
                        type="datetime-local"
                        class="w-full rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                </div>
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
                    Save Event
                </button>
            </div>
        </div>

        <!-- Navigasi bulan -->
        <div class="flex items-center justify-between rounded-xl border bg-card px-4 py-3">
            <button @click="prevMonth" class="rounded-lg p-1.5 hover:bg-accent transition-colors">
                <ChevronLeft class="h-5 w-5" />
            </button>

            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold">
                    {{ monthNames[month - 1] }} {{ year }}
                </h2>
                <button
                    @click="goToday"
                    class="rounded-md border px-3 py-1 text-xs hover:bg-accent transition-colors"
                >
                    Today
                </button>
            </div>

            <button @click="nextMonth" class="rounded-lg p-1.5 hover:bg-accent transition-colors">
                <ChevronRight class="h-5 w-5" />
            </button>
        </div>

        <!-- Grid kalender -->
        <div class="rounded-xl border bg-card overflow-hidden">
            <!-- Header hari -->
            <div class="grid grid-cols-7 border-b">
                <div
                    v-for="day in dayNames"
                    :key="day"
                    class="py-2 text-center text-xs font-medium text-muted-foreground"
                >
                    {{ day }}
                </div>
            </div>

            <!-- Grid tanggal -->
            <div class="grid grid-cols-7">
                <div
                    v-for="(cell, i) in calendarDays"
                    :key="i"
                    class="min-h-24 border-b border-r p-1.5 last:border-r-0"
                    :class="[
                        cell.date ? 'cursor-pointer hover:bg-accent/50' : 'bg-muted/30',
                        (i + 1) % 7 === 0 ? 'border-r-0' : '',
                    ]"
                    @click="cell.date && openForm(cell.date)"
                >
                    <!-- Nomor tanggal -->
                    <div
                        v-if="cell.day"
                        class="mb-1 flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium"
                        :class="isToday(cell.date!)
                            ? 'bg-primary text-primary-foreground'
                            : 'text-foreground'"
                    >
                        {{ cell.day }}
                    </div>

                    <!-- Entry di tanggal ini -->
                    <div v-if="cell.date" class="space-y-0.5">
                        <button
                            v-for="entry in entriesForDate(cell.date).slice(0, 3)"
                            :key="entry.id"
                            @click.stop="selectedEntry = entry"
                            class="w-full truncate rounded px-1.5 py-0.5 text-left text-xs transition-colors"
                            :class="entry.type === 'deadline'
                                ? 'bg-red-500/15 text-red-400 hover:bg-red-500/25'
                                : 'bg-primary/15 text-primary hover:bg-primary/25'"
                        >
                            {{ entry.title }}
                        </button>
                        <p
                            v-if="entriesForDate(cell.date).length > 3"
                            class="px-1 text-xs text-muted-foreground"
                        >
                            +{{ entriesForDate(cell.date).length - 3 }} more
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 text-xs text-muted-foreground">
            <div class="flex items-center gap-1.5">
                <div class="h-2.5 w-2.5 rounded-sm bg-primary/40" />
                <span>Event</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="h-2.5 w-2.5 rounded-sm bg-red-500/40" />
                <span>Task deadline</span>
            </div>
        </div>
    </div>

    <!-- Detail popup -->
    <Teleport to="body">
        <div
            v-if="selectedEntry"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="selectedEntry = null"
        >
            <div class="w-full max-w-sm rounded-xl border bg-card p-5 space-y-4 shadow-xl mx-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span
                            class="text-xs font-semibold uppercase tracking-wide"
                            :class="selectedEntry.type === 'deadline' ? 'text-red-400' : 'text-primary'"
                        >
                            {{ selectedEntry.type === 'deadline' ? 'Task Deadline' : 'Event' }}
                        </span>
                        <h3 class="mt-1 font-semibold">{{ selectedEntry.title }}</h3>
                    </div>
                    <button @click="selectedEntry = null" class="text-muted-foreground hover:text-foreground">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-2 text-sm text-muted-foreground">
                    <p>📁 {{ selectedEntry.project }}</p>
                    <p>🕐 {{ formatTime(selectedEntry.starts_at) }}
                        <span v-if="selectedEntry.ends_at"> – {{ formatTime(selectedEntry.ends_at) }}</span>
                    </p>
                </div>

                <div class="flex justify-end">
                    <button
                        v-if="selectedEntry.type === 'event'"
                        @click="destroy(selectedEntry!)"
                        class="rounded-lg border border-red-500/30 px-3 py-1.5 text-xs text-red-400 hover:bg-red-500/10 transition-colors"
                    >
                        Delete Event
                    </button>
                    <p v-else class="text-xs text-muted-foreground italic">
                        Delete from the task's project page.
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
    <!-- Delete Event Confirmation Modal -->
<Teleport to="body">
    <div
        v-if="eventToDelete"
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
        @click.self="cancelDelete"
    >
        <div
            class="w-full max-w-md space-y-5 rounded-2xl border border-border bg-card p-6 shadow-2xl"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-event-title"
        >
            <!-- Icon -->
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-500"
            >
                <Trash2 class="h-6 w-6" />
            </div>

            <!-- Message -->
            <div class="space-y-2">
                <h2
                    id="delete-event-title"
                    class="text-lg font-semibold"
                >
                    Delete Event?
                </h2>

                <p class="text-sm leading-relaxed text-muted-foreground">
                    Are you sure you want to delete
                    <span class="font-semibold text-foreground">
                        "{{ eventToDelete.title }}"
                    </span>?
                    This action cannot be undone.
                </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2">
                <button
                    @click="cancelDelete"
                    :disabled="deleting"
                    class="rounded-lg border px-4 py-2 text-sm font-medium transition-colors hover:bg-accent disabled:opacity-50"
                >
                    Cancel
                </button>

                <button
                    @click="confirmDelete"
                    :disabled="deleting"
                    class="flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <Trash2 class="h-4 w-4" />
                    {{ deleting ? 'Deleting...' : 'Delete Event' }}
                </button>
            </div>
        </div>
    </div>
</Teleport>
</template>