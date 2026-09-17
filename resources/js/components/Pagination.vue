<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { PaginationLink } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}>();

const visibleLinks = computed(() => props.links.filter((link) => link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;'));
const previous = computed(() => props.links.find((link) => link.label === '&laquo; Previous'));
const next = computed(() => props.links.find((link) => link.label === 'Next &raquo;'));
</script>

<template>
    <div v-if="total > 0" class="flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-xs text-muted-foreground sm:text-sm">
            Menampilkan <span class="font-medium text-foreground">{{ from ?? 0 }}</span
            >&ndash;<span class="font-medium text-foreground">{{ to ?? 0 }}</span> dari
            <span class="font-medium text-foreground">{{ total }}</span> data
        </p>

        <nav class="flex items-center gap-1" aria-label="Navigasi halaman">
            <Button variant="outline" size="icon" :class="{ 'pointer-events-none opacity-50': !previous?.url }" as-child>
                <Link v-if="previous?.url" :href="previous.url" preserve-scroll aria-label="Halaman sebelumnya">
                    <ChevronLeft class="size-4" />
                </Link>
                <span v-else aria-disabled="true"><ChevronLeft class="size-4" /></span>
            </Button>

            <template v-for="(link, index) in visibleLinks" :key="index">
                <span v-if="link.label === '...'" class="px-2 text-sm text-muted-foreground">...</span>
                <Button v-else :variant="link.active ? 'default' : 'outline'" size="sm" class="min-w-[2.25rem]" as-child>
                    <Link :href="link.url ?? '#'" preserve-scroll>{{ link.label }}</Link>
                </Button>
            </template>

            <Button variant="outline" size="icon" :class="{ 'pointer-events-none opacity-50': !next?.url }" as-child>
                <Link v-if="next?.url" :href="next.url" preserve-scroll aria-label="Halaman berikutnya">
                    <ChevronRight class="size-4" />
                </Link>
                <span v-else aria-disabled="true"><ChevronRight class="size-4" /></span>
            </Button>
        </nav>
    </div>
</template>
