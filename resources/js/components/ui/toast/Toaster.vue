<script setup lang="ts">
import { dismiss, toasts, type ToastVariant } from '@/composables/useToast';
import { CheckCircle2, Info, X, XCircle } from 'lucide-vue-next';
import type { Component } from 'vue';

const icons: Record<ToastVariant, Component> = {
    success: CheckCircle2,
    error: XCircle,
    info: Info,
};

const accentClasses: Record<ToastVariant, string> = {
    success: 'border-l-brand-green text-brand-green-dark',
    error: 'border-l-brand-danger text-brand-danger',
    info: 'border-l-primary text-primary',
};
</script>

<template>
    <Teleport to="body">
        <TransitionGroup
            tag="div"
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
            class="pointer-events-none fixed inset-x-4 top-4 z-[100] flex flex-col gap-2 sm:inset-x-auto sm:right-4 sm:top-4 sm:w-96"
            aria-live="polite"
            role="status"
        >
            <div
                v-for="item in toasts"
                :key="item.id"
                class="pointer-events-auto flex items-start gap-3 rounded-lg border border-l-4 border-border bg-card p-4 shadow-lg"
                :class="accentClasses[item.variant]"
            >
                <component :is="icons[item.variant]" class="mt-0.5 size-5 shrink-0" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-foreground">{{ item.title }}</p>
                    <p v-if="item.description" class="mt-0.5 text-sm text-muted-foreground">{{ item.description }}</p>
                </div>
                <button
                    type="button"
                    class="shrink-0 rounded-md p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    aria-label="Tutup notifikasi"
                    @click="dismiss(item.id)"
                >
                    <X class="size-4" />
                </button>
            </div>
        </TransitionGroup>
    </Teleport>
</template>
