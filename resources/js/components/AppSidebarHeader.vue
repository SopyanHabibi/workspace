<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import GlobalSearch from '@/components/GlobalSearch.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, CheckCircle2, Clock } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{ breadcrumbs?: BreadcrumbItem[] }>(),
    { breadcrumbs: () => [] },
);

interface NotifItem {
    id: number;
    title: string;
    project_name: string;
    due_at: string;
    is_overdue: boolean;
    is_today: boolean;
    is_tomorrow: boolean;
}

const page = usePage();
const notifications = computed(() => (page.props.notifications as NotifItem[]) ?? []);
const unreadCount = computed(() => notifications.value.length);

const showDropdown = ref(false);

function label(n: NotifItem): string {
    if (n.is_overdue) return 'Overdue';
    if (n.is_today) return 'Due today';
    if (n.is_tomorrow) return 'Due tomorrow';
    return 'Due soon';
}

function labelStyle(n: NotifItem): string {
    if (n.is_overdue) return 'text-red-400';
    if (n.is_today) return 'text-orange-400';
    return 'text-yellow-400';
}
</script>

<template>
    <header class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Tengah: Global Search -->
        <div class="flex-1 flex justify-center px-4">
            <GlobalSearch />
        </div>

        <!-- Bell notification -->
        <div class="relative">
            <button
                @click="showDropdown = !showDropdown"
                class="relative flex h-8 w-8 items-center justify-center rounded-lg hover:bg-accent transition-colors"
            >
                <Bell class="h-4 w-4" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
            </button>

            <!-- Dropdown -->
            <div
                v-if="showDropdown"
                class="absolute right-0 top-10 z-50 w-80 rounded-xl border bg-card shadow-xl"
            >
                <div class="flex items-center justify-between border-b px-4 py-3">
                    <h3 class="text-sm font-semibold">Notifications</h3>
                    <span class="text-xs text-muted-foreground">{{ unreadCount }} pending</span>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    <p v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-muted-foreground">
                        No pending deadlines. 🎉
                    </p>

                    <Link
                        v-for="n in notifications"
                        :key="n.id"
                        :href="`/tasks`"
                        @click="showDropdown = false"
                        class="flex items-start gap-3 border-b px-4 py-3 last:border-b-0 hover:bg-accent transition-colors"
                    >
                        <div class="mt-0.5 shrink-0">
                            <Clock v-if="!n.is_overdue" class="h-4 w-4 text-yellow-400" />
                            <CheckCircle2 v-else class="h-4 w-4 text-red-400" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ n.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ n.project_name }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-medium" :class="labelStyle(n)">
                            {{ label(n) }}
                        </span>
                    </Link>
                </div>

                <div class="border-t px-4 py-2">
                    <Link
                        href="/tasks"
                        @click="showDropdown = false"
                        class="block text-center text-xs text-primary hover:underline"
                    >
                        View all tasks →
                    </Link>
                </div>
            </div>

            <!-- Overlay untuk close dropdown -->
            <div
                v-if="showDropdown"
                class="fixed inset-0 z-40"
                @click="showDropdown = false"
            />
        </div>
    </header>
</template>