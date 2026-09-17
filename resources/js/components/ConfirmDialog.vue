<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { respondConfirm, useConfirmState } from '@/composables/useConfirm';
import { computed } from 'vue';

const state = useConfirmState();
const options = computed(() => state.options);

function handleOpenChange(value: boolean): void {
    if (!value) {
        respondConfirm(false);
    }
}
</script>

<template>
    <Dialog :open="state.open" @update:open="handleOpenChange">
        <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
            <DialogHeader>
                <DialogTitle>{{ options.title }}</DialogTitle>
                <DialogDescription v-if="options.description">{{ options.description }}</DialogDescription>
            </DialogHeader>

            <DialogFooter class="mt-2 [&>*]:w-full sm:[&>*]:w-auto">
                <Button variant="outline" @click="respondConfirm(false)">
                    {{ options.cancelLabel ?? 'Batal' }}
                </Button>
                <Button :variant="options.destructive ? 'destructive' : 'default'" @click="respondConfirm(true)">
                    {{ options.confirmLabel ?? 'Ya, Lanjutkan' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
