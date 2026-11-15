<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Download,
    File,
    FileImage,
    FileSpreadsheet,
    FileText,
    Presentation,
    Search,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface DocumentItem {
    id: number;
    original_name: string;
    category: string;
    size_formatted: string;
    mime_type: string;
    download_url: string;
    project_name: string;
    project_id: number;
    task_title: string | null;
    created_at: string;
}

interface ProjectOption {
    id: number;
    name: string;
}

const props = defineProps<{
    documents: DocumentItem[];
    projects: ProjectOption[];
    search: string | null;
    category: string | null;
    stats: { total: number; total_size: string };
}>();

const showForm = ref(false);
const searchInput = ref(props.search ?? '');

const form = useForm({
    file: null as File | null,
    project_id: null as number | null,
    task_id: null as number | null,
    category: 'other',
});

const categories = [
    { label: 'All', value: null },
    { label: 'Report', value: 'report' },
    { label: 'Presentation', value: 'presentation' },
    { label: 'Image', value: 'image' },
    { label: 'Spreadsheet', value: 'spreadsheet' },
    { label: 'Other', value: 'other' },
];

const categoryIcon: Record<string, any> = {
    report: FileText,
    presentation: Presentation,
    image: FileImage,
    spreadsheet: FileSpreadsheet,
    other: File,
};

const categoryStyle: Record<string, string> = {
    report: 'bg-blue-500/15 text-blue-400',
    presentation: 'bg-orange-500/15 text-orange-400',
    image: 'bg-green-500/15 text-green-400',
    spreadsheet: 'bg-emerald-500/15 text-emerald-400',
    other: 'bg-muted text-muted-foreground',
};

function handleFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files?.[0]) {
        form.file = target.files[0];
        // Auto-detect category dari ekstensi
        const ext = target.files[0].name.split('.').pop()?.toLowerCase();
        if (['pdf', 'doc', 'docx'].includes(ext ?? '')) form.category = 'report';
        else if (['ppt', 'pptx'].includes(ext ?? '')) form.category = 'presentation';
        else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext ?? '')) form.category = 'image';
        else if (['xls', 'xlsx', 'csv'].includes(ext ?? '')) form.category = 'spreadsheet';
        else form.category = 'other';
    }
}

function submit() {
    form.post('/documents', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            showForm.value = false;
        },
    });
}

function filterCategory(value: string | null) {
    router.get('/documents', {
        ...(searchInput.value ? { search: searchInput.value } : {}),
        ...(value ? { category: value } : {}),
    }, { preserveState: true });
}

function doSearch() {
    router.get('/documents', {
        ...(searchInput.value ? { search: searchInput.value } : {}),
        ...(props.category ? { category: props.category } : {}),
    }, { preserveState: true });
}

function destroy(doc: DocumentItem) {
    if (confirm(`Delete "${doc.original_name}"?`)) {
        router.delete(`/documents/${doc.id}`, { preserveScroll: true });
    }
}

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric',
    });
}
</script>

<template>
    <Head title="Documents" />

    <div class="mx-auto w-full max-w-6xl space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Documents</h1>
                <p class="text-sm text-muted-foreground">Manage and organize your project files.</p>
            </div>
            <button
                @click="showForm = !showForm"
                class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                <Upload class="h-4 w-4" />
                Upload
            </button>
        </div>

        <!-- Upload form -->
        <div v-if="showForm" class="rounded-xl border bg-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Upload Document</h2>
                <button @click="showForm = false" class="text-muted-foreground hover:text-foreground">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- File drop zone -->
            <label class="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-border p-8 hover:border-primary/50 hover:bg-accent/50 transition-colors">
                <Upload class="mb-2 h-8 w-8 text-muted-foreground" />
                <p class="text-sm font-medium">
                    {{ form.file ? form.file.name : 'Click to upload or drag & drop' }}
                </p>
                <p class="text-xs text-muted-foreground mt-1">PDF, DOCX, PPTX, XLSX, images — max 20MB</p>
                <input type="file" class="hidden" @change="handleFileChange" />
            </label>
            <p v-if="form.errors.file" class="text-xs text-red-500">{{ form.errors.file }}</p>

            <div class="grid gap-3 sm:grid-cols-3">
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
                    <label class="text-xs text-muted-foreground">Category</label>
                    <select
                        v-model="form.category"
                        class="w-full rounded-lg border bg-card px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="report">Report</option>
                        <option value="presentation">Presentation</option>
                        <option value="image">Image</option>
                        <option value="spreadsheet">Spreadsheet</option>
                        <option value="other">Other</option>
                    </select>
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
                    :disabled="form.processing || !form.file"
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                >
                    {{ form.processing ? 'Uploading...' : 'Upload' }}
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border bg-card p-4">
                <p class="text-sm text-muted-foreground">Total Documents</p>
                <p class="mt-1 text-3xl font-bold">{{ stats.total }}</p>
            </div>
            <div class="rounded-xl border bg-card p-4">
                <p class="text-sm text-muted-foreground">Total Size</p>
                <p class="mt-1 text-3xl font-bold">{{ stats.total_size }}</p>
            </div>
        </div>

        <!-- Search -->
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input
                    v-model="searchInput"
                    placeholder="Search documents..."
                    class="w-full rounded-lg border bg-card py-2 pl-9 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    @keydown.enter="doSearch"
                />
            </div>
            <button
                @click="doSearch"
                class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                Search
            </button>
        </div>

        <!-- Category filter -->
        <div class="flex flex-wrap gap-2">
            <button
                v-for="cat in categories"
                :key="cat.label"
                @click="filterCategory(cat.value)"
                class="rounded-full border px-4 py-1.5 text-sm transition-colors"
                :class="category === cat.value
                    ? 'bg-primary text-primary-foreground border-primary font-medium'
                    : 'text-muted-foreground hover:text-foreground'"
            >
                {{ cat.label }}
            </button>
        </div>

        <!-- Empty state -->
        <p v-if="documents.length === 0" class="text-sm text-muted-foreground">
            No documents found.
        </p>

        <!-- Document list -->
        <div class="space-y-2">
            <div
                v-for="doc in documents"
                :key="doc.id"
                class="flex items-center gap-4 rounded-xl border bg-card p-4 hover:border-primary/30 transition-colors"
            >
                <!-- Icon kategori -->
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                    :class="categoryStyle[doc.category]"
                >
                    <component :is="categoryIcon[doc.category]" class="h-5 w-5" />
                </div>

                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium text-sm">{{ doc.original_name }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ doc.project_name }}
                        <span v-if="doc.task_title"> · {{ doc.task_title }}</span>
                        · {{ doc.size_formatted }}
                        · {{ formatDate(doc.created_at) }}
                    </p>
                </div>

                <!-- Category badge -->
                <span
                    class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize hidden sm:block"
                    :class="categoryStyle[doc.category]"
                >
                    {{ doc.category }}
                </span>

                <!-- Actions -->
                <div class="flex shrink-0 items-center gap-1">
                    <a
                        :href="doc.download_url"
                        class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-accent transition-colors"
                        title="Download"
                    >
                        <Download class="h-4 w-4" />
                    </a>
                    <button
                        @click="destroy(doc)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-red-500/10 hover:text-red-400 transition-colors"
                        title="Delete"
                    >
                        <Trash2 class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>