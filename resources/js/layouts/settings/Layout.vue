<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import PageContainer from '@/components/PageContainer.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profil',
        href: '/settings/profile',
    },
    {
        title: 'Password',
        href: '/settings/password',
    },
];

const page = usePage();
const currentPath = computed(() => page.url);
</script>

<template>
    <PageContainer>
        <Heading title="Pengaturan" description="Kelola profil dan keamanan akun Anda" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-row gap-1 overflow-x-auto lg:flex-col lg:gap-0 lg:space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-auto shrink-0 justify-start lg:w-full', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href!">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </PageContainer>
</template>
