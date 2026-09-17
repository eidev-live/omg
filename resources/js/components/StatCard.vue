<script setup lang="ts">
import type { Component } from 'vue';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        title: string;
        value: string;
        hint?: string;
        icon?: Component;
        tone?: 'default' | 'primary' | 'success' | 'warning' | 'danger';
        loading?: boolean;
    }>(),
    {
        hint: undefined,
        icon: undefined,
        tone: 'default',
        loading: false,
    },
);

const toneClasses: Record<string, string> = {
    default: 'text-foreground',
    primary: 'text-primary',
    success: 'text-brand-green-dark',
    warning: 'text-[#8A6400]',
    danger: 'text-brand-danger',
};

const valueClass = computed(() => toneClasses[props.tone] ?? toneClasses.default);
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
            <component :is="icon" v-if="icon" class="size-4 shrink-0 text-muted-foreground" />
        </div>
        <p v-if="loading" class="mt-3 h-7 w-24 animate-pulse rounded bg-muted" />
        <p v-else class="mt-2 text-xl font-semibold tracking-tight md:text-2xl" :class="valueClass">{{ value }}</p>
        <p v-if="hint && !loading" class="mt-1 text-xs text-muted-foreground">{{ hint }}</p>
    </div>
</template>
