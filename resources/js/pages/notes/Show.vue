<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Pin, PinOff, Trash2 } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { ref } from 'vue';

interface NoteDetail {
    id: number;
    title: string;
    content: string | null;
    type: 'personal' | 'project' | 'task';
    is_pinned: boolean;
    project: { id: number; name: string };
    task: { id: number; title: string } | null;
    updated_at: string;
}

const props = defineProps<{ note: NoteDetail }>();

const form = useForm({
    title: props.note.title,
    content: props.note.content ?? '',
    type: props.note.type,
});

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

function save() {
    form.patch(`/notes/${props.note.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Note saved successfully.');
        },
    });
}

function togglePin() {
    router.patch(
        `/notes/${props.note.id}/pin`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success(props.note.is_pinned ? 'Note unpinned.' : 'Note pinned.');
            },
        },
    );
}

function destroy() {
    if (confirm(`Delete note "${props.note.title}"?`)) {
        router.delete(`/notes/${props.note.id}`, {
            onSuccess: () => router.visit('/notes'),
        });
    }
}

</script>

<template>
    <Head :title="note.title" />

    <div class="mx-auto w-full max-w-3xl space-y-6 p-6">
        <!-- Top bar -->
        <div class="flex items-center justify-between">
            <Link
                href="/notes"
                class="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to Notes
            </Link>

            <div class="flex items-center gap-2">
                <button
                    @click="togglePin"
                    class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs transition-colors"
                    :class="note.is_pinned
                        ? 'border-primary/50 bg-primary/10 text-primary'
                        : 'text-muted-foreground hover:text-foreground'"
                >
                    <Pin v-if="!note.is_pinned" class="h-3.5 w-3.5" />
                    <PinOff v-else class="h-3.5 w-3.5" />
                    {{ note.is_pinned ? 'Unpin' : 'Pin' }}
                </button>

                <button
                    @click="destroy"
                    class="flex items-center gap-1.5 rounded-lg border border-red-500/30 px-3 py-1.5 text-xs text-red-400 hover:bg-red-500/10 transition-colors"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                    Delete
                </button>
            </div>
        </div>

        <!-- Editor card -->
        <div class="rounded-xl border bg-card p-6 space-y-4">
            <!-- Type badge + project info -->
            <div class="flex items-center justify-between">
                <span
                    class="rounded-full px-2.5 py-0.5 text-xs font-semibold tracking-wide uppercase"
                    :class="typeStyle[note.type]"
                >
                    {{ typeLabel[note.type] }}
                </span>
                <span class="text-xs text-muted-foreground">
                    {{ note.project.name }}
                    <span v-if="note.task"> · {{ note.task.title }}</span>
                </span>
            </div>

            <!-- Title -->
            <input
                v-model="form.title"
                placeholder="Note title"
                class="w-full bg-transparent text-2xl font-bold focus:outline-none placeholder:text-muted-foreground"
            />

            <div class="border-t border-border" />

            <!-- Content -->
            <textarea
                v-model="form.content"
                placeholder="Start writing your note here..."
                rows="16"
                class="w-full bg-transparent text-sm leading-relaxed text-foreground focus:outline-none placeholder:text-muted-foreground resize-none"
            />
        </div>

        <!-- Save button + status -->
        <div class="flex items-center justify-between">
            <p class="text-xs text-muted-foreground">
                Last updated {{ new Date(note.updated_at).toLocaleString('id-ID') }}
            </p>
            <button
                @click="save"
                :disabled="form.processing || !form.isDirty"
                class="rounded-lg bg-primary px-5 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50 transition-colors"
            >
                {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
    </div>
</template>