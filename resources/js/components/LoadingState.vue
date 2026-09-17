<script setup lang="ts">
import { LoaderCircle } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        label?: string;
    }>(),
    {
        label: 'Memuat data...',
    },
);
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-border bg-card px-6 py-12 text-center">
        <LoaderCircle class="size-6 animate-spin text-primary" />
        <p class="text-sm text-muted-foreground">{{ label }}</p>
    </div>
</template>
