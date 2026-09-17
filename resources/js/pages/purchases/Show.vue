<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatDateTime, formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Ban } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Movement = {
    id: number;
    movement_date: string;
    movement_type: string;
    movement_label: string;
    quantity: number;
    notes: string | null;
};

type PurchaseDetail = {
    id: number;
    purchase_number: string;
    purchase_date: string;
    quantity: number;
    egg_quantity: number;
    total_cost: number;
    cost_per_egg: number;
    notes: string | null;
    status: string;
    cancelled_at: string | null;
    cancel_reason: string | null;
    created_by: string | null;
    layer: { quantity_received: number; quantity_remaining: number; unit_cost: number } | null;
    movements: Movement[];
};

const props = defineProps<{
    purchase: PurchaseDetail;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pembelian', href: '/purchases' },
    { title: props.purchase.purchase_number, href: '#' },
];

const movementColumns: DataTableColumn[] = [
    { key: 'movement_label', label: 'Jenis', primary: true },
    { key: 'movement_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity', label: 'Jumlah', type: 'number', align: 'right' },
    { key: 'notes', label: 'Catatan' },
];

const isCancelled = computed(() => props.purchase.status === 'CANCELLED');
const canCancel = computed(
    () => !isCancelled.value && props.purchase.layer !== null && props.purchase.layer.quantity_remaining === props.purchase.layer.quantity_received,
);

const dialogOpen = ref(false);
const reason = ref('');
const processing = ref(false);

function submitCancel(): void {
    processing.value = true;

    router.patch(
        route('purchases.cancel', { purchase: props.purchase.id }),
        { reason: reason.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                dialogOpen.value = false;
                reason.value = '';
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`Pembelian ${purchase.purchase_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader :title="purchase.purchase_number" :description="`Dibuat ${formatDate(purchase.purchase_date)}`">
                <template #actions>
                    <StatusBadge :status="purchase.status" />
                    <Button v-if="canCancel" variant="destructive" @click="dialogOpen = true">
                        <Ban class="size-4" />
                        Batalkan
                    </Button>
                </template>
            </PageHeader>

            <div v-if="isCancelled" class="rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-4 text-sm text-brand-danger">
                Pembelian dibatalkan{{ purchase.cancelled_at ? ` pada ${formatDateTime(purchase.cancelled_at)}` : '' }}.
                <span v-if="purchase.cancel_reason">Alasan: {{ purchase.cancel_reason }}</span>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm md:p-6">
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-muted-foreground">Jumlah ikat</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatNumber(purchase.quantity) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Total telur</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatNumber(purchase.egg_quantity) }} butir</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Total biaya</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatRupiah(purchase.total_cost) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">HPP / butir</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatRupiah(purchase.cost_per_egg) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Dicatat oleh</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ purchase.created_by ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Catatan</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ purchase.notes ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div v-if="purchase.layer" class="rounded-xl border border-border bg-card p-4 shadow-sm md:p-6">
                <h2 class="text-sm font-semibold text-foreground">Inventory Layer</h2>
                <dl class="mt-3 grid grid-cols-3 gap-4">
                    <div>
                        <dt class="text-xs text-muted-foreground">Diterima</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatNumber(purchase.layer.quantity_received) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Sisa</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatNumber(purchase.layer.quantity_remaining) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">HPP / butir</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatRupiah(purchase.layer.unit_cost) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-foreground">Pergerakan stok</h2>
                <DataTable :columns="movementColumns" :rows="purchase.movements" row-key="id" empty-title="Belum ada pergerakan stok" />
            </div>

            <Dialog v-model:open="dialogOpen">
                <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
                    <DialogHeader>
                        <DialogTitle>Batalkan pembelian?</DialogTitle>
                        <DialogDescription>
                            Pembelian {{ purchase.purchase_number }} akan dibatalkan dan stok sebanyak {{ formatNumber(purchase.egg_quantity) }} butir
                            dikurangi kembali.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="cancel_reason">Alasan (opsional)</Label>
                        <Textarea id="cancel_reason" v-model="reason" rows="2" placeholder="Contoh: salah input jumlah" />
                    </div>

                    <DialogFooter class="[&>*]:w-full sm:[&>*]:w-auto">
                        <Button variant="outline" @click="dialogOpen = false">Batal</Button>
                        <Button variant="destructive" :disabled="processing" @click="submitCancel">Ya, Batalkan</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </PageContainer>
    </AppLayout>
</template>
