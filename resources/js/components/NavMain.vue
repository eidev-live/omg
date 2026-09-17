<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import type { Component } from 'vue';

interface NavMenuItem {
    title: string;
    href: string;
    icon: Component;
}

interface NavSection {
    label: string;
    items: NavMenuItem[];
}

defineProps<{
    sections: NavSection[];
}>();

const page = usePage<SharedData>();

function isActive(href: string): boolean {
    return page.url === href || page.url.startsWith(`${href}/`);
}
</script>

<template>
    <SidebarGroup v-for="section in sections" :key="section.label" class="px-2 py-0">
        <SidebarGroupLabel>{{ section.label }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in section.items" :key="item.href">
                <SidebarMenuButton as-child :is-active="isActive(item.href)">
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
