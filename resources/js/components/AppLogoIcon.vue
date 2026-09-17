<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg :class="className" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" v-bind="$attrs">
        <path d="M12 2.5C7.86 2.5 4.5 8.36 4.5 12.5a7.5 7.5 0 0 0 15 0c0-4.14-3.36-10-7.5-10Z" />
    </svg>
</template>
