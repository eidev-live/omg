<script setup lang="ts">
import AppLogoImage from '@/components/AppLogoImage.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col items-center gap-3">
                    <Link :href="route('home')" class="flex items-center">
                        <AppLogoImage class="h-32 w-auto" />
                    </Link>
                    <div class="space-y-1 text-center">
                        <h1 v-if="title" class="text-xl font-semibold tracking-tight">{{ title }}</h1>
                        <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
