<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AppLogoImage from '@/components/AppLogoImage.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <AppLogoImage class="h-11 w-auto max-w-[11rem] object-contain group-data-[collapsible=icon]:hidden" />
    <span class="hidden size-8 shrink-0 items-center justify-center rounded-lg bg-brand-yellow text-brand-ink group-data-[collapsible=icon]:flex">
        <AppLogoIcon class="size-5" />
    </span>
</template>
