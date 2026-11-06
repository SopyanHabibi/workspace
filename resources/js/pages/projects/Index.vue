<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Project {
    id: number;
    name: string;
    description: string | null;
    status: string;
    due_date: string | null;
    tasks_count: number;
    done_tasks_count: number;
}

defineProps<{ projects: Project[] }>();

const breadcrumbs = [{ title: 'Projects', href: '/projects' }];

const form = useForm({ name: '', description: '', due_date: '' });

function submit() {
    form.post('/projects', { onSuccess: () => form.reset() });
}

function progress(p: Project): number {
    return p.tasks_count ? Math.round((p.done_tasks_count / p.tasks_count) * 100) : 0;
}
</script>

<template>
    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-4xl space-y-6 p-4">
            <form @submit.prevent="submit" class="space-y-3 rounded-lg border p-4">
                <h2 class="font-semibold">Proyek baru</h2>

                <div>
                    <input v-model="form.name" placeholder="Nama proyek" class="w-full rounded border bg-transparent px-3 py-2" />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <textarea v-model="form.description" placeholder="Deskripsi (opsional)" rows="2" class="w-full rounded border bg-transparent px-3 py-2" />

                <div class="flex items-center gap-3">
                    <input v-model="form.due_date" type="date" class="rounded border bg-transparent px-3 py-2" />
                    <button :disabled="form.processing" class="rounded bg-black px-4 py-2 text-white disabled:opacity-50 dark:bg-white dark:text-black">
                        Simpan
                    </button>
                </div>
            </form>

            <p v-if="projects.length === 0" class="text-sm text-gray-500">Belum ada proyek.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <Link v-for="p in projects" :key="p.id" :href="`/projects/${p.id}`" class="block space-y-2 rounded-lg border p-4 hover:bg-gray-50 dark:hover:bg-gray-900">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold">{{ p.name }}</h3>
                        <span class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800">{{ p.status }}</span>
                    </div>

                    <p v-if="p.description" class="line-clamp-2 text-sm text-gray-500">{{ p.description }}</p>

                    <div class="h-2 w-full overflow-hidden rounded bg-gray-200 dark:bg-gray-800">
                        <div class="h-full bg-green-500" :style="{ width: progress(p) + '%' }" />
                    </div>

                    <p class="text-xs text-gray-500">
                        {{ p.done_tasks_count }}/{{ p.tasks_count }} tugas selesai ({{ progress(p) }}%)
                        <span v-if="p.due_date"> · Deadline {{ p.due_date.slice(0, 10) }}</span>
                    </p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>