<script setup lang="ts">
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: Component;
    }>(),
    {
        description: undefined,
        icon: undefined,
    },
);
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border bg-card px-6 py-12 text-center">
        <div v-if="icon" class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-accent text-accent-foreground">
            <component :is="icon" class="size-6" />
        </div>
        <h3 class="text-base font-semibold text-foreground">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-muted-foreground">{{ description }}</p>
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>
