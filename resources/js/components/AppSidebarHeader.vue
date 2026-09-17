<script setup lang="ts">
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-2 border-b border-border/70 bg-background/95 px-4 backdrop-blur transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:h-16 md:px-6"
    >
        <SidebarTrigger class="-ml-1 shrink-0" />
        <template v-if="breadcrumbs && breadcrumbs.length > 0">
            <Breadcrumb class="min-w-0">
                <BreadcrumbList class="flex-nowrap overflow-hidden">
                    <template v-for="(item, index) in breadcrumbs" :key="index">
                        <BreadcrumbItem :class="index === breadcrumbs.length - 1 ? 'min-w-0' : 'hidden shrink-0 sm:flex'">
                            <template v-if="index === breadcrumbs.length - 1">
                                <BreadcrumbPage class="truncate">{{ item.title }}</BreadcrumbPage>
                            </template>
                            <template v-else>
                                <BreadcrumbLink :href="item.href">
                                    {{ item.title }}
                                </BreadcrumbLink>
                            </template>
                        </BreadcrumbItem>
                        <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" class="hidden sm:block" />
                    </template>
                </BreadcrumbList>
            </Breadcrumb>
        </template>
    </header>
</template>
