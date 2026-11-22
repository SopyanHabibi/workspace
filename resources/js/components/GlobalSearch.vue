<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import {
    FileText,
    FolderKanban,
    Loader2,
    NotebookPen,
    Search,
    X,
    ListTodo,
} from "lucide-vue-next";
import { nextTick, ref, watch } from "vue";

interface SearchResult {
    id: number;
    type: "project" | "task" | "note" | "document";
    title: string;
    subtitle: string;
    url: string;
}

const isOpen = ref(false);
const query = ref("");
const results = ref<SearchResult[]>([]);
const loading = ref(false);
const selectedIndex = ref(-1);
const inputRef = ref<HTMLInputElement | null>(null);

const typeIcon: Record<string, any> = {
    project: FolderKanban,
    task: ListTodo,
    note: NotebookPen,
    document: FileText,
};

const typeStyle: Record<string, string> = {
    project: "bg-primary/15 text-primary",
    task: "bg-yellow-500/15 text-yellow-400",
    note: "bg-purple-500/15 text-purple-400",
    document: "bg-blue-500/15 text-blue-400",
};

const typeLabel: Record<string, string> = {
    project: "Project",
    task: "Task",
    note: "Note",
    document: "Document",
};

let debounceTimer: ReturnType<typeof setTimeout>;

watch(query, (val) => {
    selectedIndex.value = -1;
    clearTimeout(debounceTimer);

    if (val.length < 2) {
        results.value = [];
        loading.value = false;
        return;
    }

    loading.value = true;
    debounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/search?q=${encodeURIComponent(val)}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            results.value = await res.json();
        } catch {
            results.value = [];
        } finally {
            loading.value = false;
        }
    }, 300);
});

async function open() {
    isOpen.value = true;
    query.value = "";
    results.value = [];
    await nextTick();
    inputRef.value?.focus();
}
function close() {
    isOpen.value = false;
    query.value = "";
    results.value = [];
    selectedIndex.value = -1;
}

function navigate(result: SearchResult) {
    close();
    router.visit(result.url);
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === "ArrowDown") {
        e.preventDefault();
        selectedIndex.value = Math.min(
            selectedIndex.value + 1,
            results.value.length - 1,
        );
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
    } else if (e.key === "Enter" && selectedIndex.value >= 0) {
        navigate(results.value[selectedIndex.value]);
    } else if (e.key === "Escape") {
        close();
    }
}

// Ctrl+K / Cmd+K untuk buka search
function onGlobalKeydown(e: KeyboardEvent) {
    if ((e.ctrlKey || e.metaKey) && e.key === "k") {
        e.preventDefault();
        isOpen.value ? close() : open();
    }
}

// Register global shortcut
if (typeof window !== "undefined") {
    window.addEventListener("keydown", onGlobalKeydown);
}
</script>

<template>
    <!-- Trigger button -->
    <button
        @click="open"
        class="flex items-center gap-2 rounded-lg border bg-muted/50 px-3 py-1.5 text-sm text-muted-foreground hover:bg-accent transition-colors"
    >
        <Search class="h-3.5 w-3.5" />
        <span class="hidden sm:block">Search...</span>
        <kbd
            class="hidden sm:block rounded border bg-background px-1.5 py-0.5 text-xs"
            >Ctrl K</kbd
        >
    </button>

    <!-- Modal overlay -->
    <Teleport to="body">
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4"
        >
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/60" @click="close" />

            <!-- Search panel -->
            <div
                class="relative w-full max-w-lg rounded-xl border bg-card shadow-2xl"
            >
                <!-- Input -->
                <div class="flex items-center gap-3 border-b px-4 py-3">
                    <Search class="h-4 w-4 shrink-0 text-muted-foreground" />
                    <input
                        ref="inputRef"
                        v-model="query"
                        placeholder="Search projects, tasks, notes, documents..."
                        class="flex-1 bg-transparent text-sm focus:outline-none placeholder:text-muted-foreground"
                        @keydown="onKeydown"
                    />
                    <div class="flex items-center gap-2">
                        <Loader2
                            v-if="loading"
                            class="h-4 w-4 animate-spin text-muted-foreground"
                        />
                        <button
                            v-if="query"
                            @click="query = ''"
                            class="text-muted-foreground hover:text-foreground"
                        >
                            <X class="h-4 w-4" />
                        </button>
                        <kbd
                            @click="close"
                            class="cursor-pointer rounded border bg-muted px-1.5 py-0.5 text-xs text-muted-foreground hover:bg-accent"
                        >
                            Esc
                        </kbd>
                    </div>
                </div>

                <!-- Results -->
                <div class="max-h-80 overflow-y-auto py-2">
                    <!-- Empty state -->
                    <p
                        v-if="query.length < 2"
                        class="px-4 py-6 text-center text-sm text-muted-foreground"
                    >
                        Type at least 2 characters to search.
                    </p>

                    <p
                        v-else-if="!loading && results.length === 0"
                        class="px-4 py-6 text-center text-sm text-muted-foreground"
                    >
                        No results for "{{ query }}".
                    </p>

                    <!-- Result items -->
                    <button
                        v-for="(result, i) in results"
                        :key="`${result.type}-${result.id}`"
                        @click="navigate(result)"
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left transition-colors"
                        :class="
                            selectedIndex === i
                                ? 'bg-accent'
                                : 'hover:bg-accent/50'
                        "
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                            :class="typeStyle[result.type]"
                        >
                            <component
                                :is="typeIcon[result.type]"
                                class="h-4 w-4"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {{ result.title }}
                            </p>
                            <p class="truncate text-xs text-muted-foreground">
                                {{ result.subtitle }}
                            </p>
                        </div>
                        <span
                            class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="typeStyle[result.type]"
                        >
                            {{ typeLabel[result.type] }}
                        </span>
                    </button>
                </div>

                <!-- Footer -->
                <div
                    class="border-t px-4 py-2 flex items-center gap-3 text-xs text-muted-foreground"
                >
                    <span>↑↓ navigate</span>
                    <span>↵ select</span>
                    <span>Esc close</span>
                </div>
            </div>
        </div>
    </Teleport>
</template>
