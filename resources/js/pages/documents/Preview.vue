<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Download,
    File,
    FileImage,
    FileSpreadsheet,
    FileText,
    Presentation,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

interface DocumentDetail {
    id: number;
    original_name: string;
    category: string;
    size_formatted: string;
    mime_type: string;
    download_url: string;
    preview_url: string;
    project_name: string;
    task_title: string | null;
    created_at: string;
    previewable: boolean;
    preview_type: 'pdf' | 'image' | 'text' | 'unsupported';
}

const props = defineProps<{ document: DocumentDetail }>();

const textContent = ref('');
const loadingText = ref(false);

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

const unsupportedExtensions: Record<string, string> = {
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'DOCX',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'XLSX',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PPTX',
    'application/zip': 'ZIP',
    'application/x-rar-compressed': 'RAR',
};

function formatDate(value: string): string {
    return new Date(value).toLocaleDateString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric',
    });
}

onMounted(async () => {
    if (props.document.preview_type === 'text') {
        loadingText.value = true;
        try {
            const res = await fetch(props.document.preview_url);
            textContent.value = await res.text();
        } catch {
            textContent.value = 'Failed to load file content.';
        } finally {
            loadingText.value = false;
        }
    }
});
</script>

<template>
    <Head :title="`Preview — ${document.original_name}`" />

    <div class="mx-auto w-full max-w-5xl space-y-4 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4">
            <Link
                href="/documents"
                class="flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to Documents
            </Link>

            <a
                :href="document.download_url"
                class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
                <Download class="h-4 w-4" />
                Download
            </a>
        </div>

        <!-- File info card -->
        <div class="rounded-xl border bg-card p-4">
            <div class="flex items-center gap-4">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                    :class="categoryStyle[document.category]"
                >
                    <component :is="categoryIcon[document.category]" class="h-6 w-6" />
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate font-semibold">{{ document.original_name }}</h1>
                    <p class="text-xs text-muted-foreground">
                        {{ document.project_name }}
                        <span v-if="document.task_title"> · {{ document.task_title }}</span>
                        · {{ document.size_formatted }}
                        · {{ formatDate(document.created_at) }}
                    </p>
                </div>

                <span
                    class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                    :class="categoryStyle[document.category]"
                >
                    {{ document.category }}
                </span>
            </div>
        </div>

        <!-- Preview area -->
        <div class="rounded-xl border bg-card overflow-hidden">
            <!-- PDF -->
            <template v-if="document.preview_type === 'pdf'">
                <div class="border-b px-4 py-2 flex items-center gap-2">
                    <FileText class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground">PDF Document</span>
                </div>
                <iframe
                    :src="document.preview_url"
                    class="w-full"
                    style="height: 75vh;"
                    frameborder="0"
                />
            </template>

            <!-- Image -->
            <template v-else-if="document.preview_type === 'image'">
                <div class="border-b px-4 py-2 flex items-center gap-2">
                    <FileImage class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground">Image</span>
                </div>
                <div class="flex items-center justify-center bg-muted/30 p-6" style="min-height: 400px;">
                    <img
                        :src="document.preview_url"
                        :alt="document.original_name"
                        class="max-h-[70vh] max-w-full rounded-lg object-contain shadow-md"
                    />
                </div>
            </template>

            <!-- Text -->
            <template v-else-if="document.preview_type === 'text'">
                <div class="border-b px-4 py-2 flex items-center gap-2">
                    <FileText class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground">Text File</span>
                </div>
                <div class="p-6" style="min-height: 400px;">
                    <p v-if="loadingText" class="text-sm text-muted-foreground">Loading...</p>
                    <pre v-else class="whitespace-pre-wrap font-mono text-sm leading-relaxed text-foreground">{{ textContent }}</pre>
                </div>
            </template>

            <!-- Unsupported -->
            <template v-else>
                <div class="flex flex-col items-center justify-center gap-4 p-12" style="min-height: 300px;">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl"
                        :class="categoryStyle[document.category]"
                    >
                        <component :is="categoryIcon[document.category]" class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <p class="font-semibold">
                            Preview not available for
                            {{ unsupportedExtensions[document.mime_type] ?? document.mime_type }}
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            This file type cannot be previewed in the browser.
                            Download it to open with the appropriate application.
                        </p>
                    </div>
                    <a
                        :href="document.download_url"
                        class="flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                    >
                        <Download class="h-4 w-4" />
                        Download File
                    </a>
                </div>
            </template>
        </div>
    </div>
</template>