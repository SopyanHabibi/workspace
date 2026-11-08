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
        <!-- SidebarHeader dibiarkan standar, tapi garis pembatas kita buat manual via div di bawahnya dengan negative margin agar mentok full -->
        <SidebarHeader class="px-4 py-3 group-data-[collapsible=icon]:px-2 group-data-[collapsible=icon]:py-3 relative">
            <div class="flex items-center gap-2 group-data-[collapsible=icon]:justify-center w-full">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary">
                    <Boxes class="h-4 w-4 text-primary-foreground" />
                </div>
                <span class="font-semibold tracking-tight group-data-[collapsible=icon]:hidden">WorkSpace</span>
            </div>
            <!-- Garis border manual full width menggunakan negative margin agar menembus padding header -->
            <div class="absolute bottom-0 left-0 right-0 h-[1px] bg-sidebar-border"></div>
        </SidebarHeader>

        <SidebarContent>
            <SidebarGroup>
                <SidebarGroupLabel class="group-data-[collapsible=icon]:hidden">Workspace</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <div class="flex items-center gap-2 px-2 py-1.5 text-sm text-muted-foreground group-data-[collapsible=icon]:justify-center">
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
                            class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:justify-center"
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

        <!-- SidebarFooter dengan garis border manual full width -->
        <SidebarFooter class="px-2 py-2 relative">
            <!-- Garis border manual atas -->
            <div class="absolute top-0 left-0 right-0 h-[1px] bg-sidebar-border"></div>
            
            <SidebarMenu>
                <SidebarMenuItem>
                    <Link
                        href="/settings"
                        class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-sidebar-foreground transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:justify-center"
                    >
                        <Settings class="h-4 w-4 shrink-0" />
                        <span class="group-data-[collapsible=icon]:hidden">Settings</span>
                    </Link>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>