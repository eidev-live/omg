<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { SlidersHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';

withDefaults(
    defineProps<{
        title?: string;
        activeCount?: number;
    }>(),
    {
        title: 'Filter',
        activeCount: 0,
    },
);

const emit = defineEmits<{
    (e: 'apply'): void;
    (e: 'reset'): void;
}>();

const open = ref(false);

function apply(): void {
    emit('apply');
    open.value = false;
}
</script>

<template>
    <Button variant="outline" @click="open = true">
        <SlidersHorizontal class="size-4" />
        {{ title }}
        <span
            v-if="activeCount > 0"
            class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-xs font-medium text-primary-foreground"
        >
            {{ activeCount }}
        </span>
    </Button>

    <Dialog v-model:open="open">
        <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
            </DialogHeader>

            <div class="grid gap-4">
                <slot />
            </div>

            <DialogFooter class="[&>*]:w-full sm:[&>*]:w-auto">
                <Button type="button" variant="outline" @click="emit('reset')">Reset</Button>
                <Button type="button" @click="apply">Terapkan</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
