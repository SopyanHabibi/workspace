<script setup lang="ts">
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import {
    CalendarDays,
    FolderKanban,
    LayoutDashboard,
    ListTodo,
    NotebookPen,
    Settings,
    Boxes,
} from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user as { name: string } | undefined);
const currentPath = computed(() => page.url);

function isActive(href: string): boolean {
    if (href === '/dashboard') return currentPath.value === '/dashboard';
    return currentPath.value.startsWith(href);
}

const menuItems = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { title: 'Projects', href: '/projects', icon: FolderKanban },
    { title: 'All Tasks', href: '/tasks', icon: ListTodo },
    { title: 'Notes', href: '/notes', icon: NotebookPen },
    { title: 'Calendar', href: '/calendar', icon: CalendarDays },
];
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader class="border-b border-sidebar-border px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary">
                    <Boxes class="h-4 w-4 text-primary-foreground" />
                </div>
                <span class="font-semibold tracking-tight group-data-[collapsible=icon]:hidden">WorkSpace</span>
            </div>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupLabel class="group-data-[collapsible=icon]:hidden">Workspace</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <div class="flex items-center gap-2 px-2 py-1.5 text-sm text-muted-foreground">
                            <Boxes class="h-4 w-4 shrink-0" />
                            <span class="group-data-[collapsible=icon]:hidden">{{ user?.name ?? 'My Workspace' }}</span>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>

            <SidebarGroup>
                <SidebarGroupLabel class="group-data-[collapsible=icon]:hidden">Menu</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem v-for="item in menuItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                            :class="isActive(item.href)
                                ? 'bg-primary/15 text-primary font-medium'
                                : 'text-sidebar-foreground'"
                        >
                            <component :is="item.icon" class="h-4 w-4 shrink-0" />
                            <span class="group-data-[collapsible=icon]:hidden">{{ item.title }}</span>
                        </Link>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border">
            <SidebarMenu>
                <SidebarMenuItem>
                    <Link
                        href="/settings"
                        class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-sidebar-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                    >
                        <Settings class="h-4 w-4 shrink-0" />
                        <span class="group-data-[collapsible=icon]:hidden">Settings</span>
                    </Link>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>