<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Boxes, CircleCheck, Layers, Plus, Wallet } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type LayerRow = {
    id: number;
    purchase_number: string | null;
    purchase_date: string | null;
    quantity_received: number;
    quantity_remaining: number;
    unit_cost: number;
};

type MovementRow = {
    id: number;
    movement_date: string;
    movement_type: string;
    movement_label: string;
    quantity: number;
    notes: string | null;
};

const props = defineProps<{
    stock: {
        current: number;
        layers_remaining: number;
        consistent: boolean;
        inventory_value: number;
        minimum_stock: number;
    };
    layers: Paginator<LayerRow>;
    movements: Paginator<MovementRow>;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Stock', href: '/stock' }];

const layerColumns: DataTableColumn[] = [
    { key: 'purchase_number', label: 'Batch', primary: true },
    { key: 'purchase_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity_received', label: 'Diterima', type: 'number', align: 'right' },
    { key: 'quantity_remaining', label: 'Sisa', type: 'number', align: 'right' },
    { key: 'unit_cost', label: 'HPP / Butir', type: 'currency', align: 'right' },
];

const movementColumns: DataTableColumn[] = [
    { key: 'movement_label', label: 'Jenis', primary: true },
    { key: 'movement_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity', label: 'Jumlah', type: 'number', align: 'right' },
    { key: 'notes', label: 'Catatan' },
];

const isLowStock = computed(() => props.stock.current <= props.stock.minimum_stock);

const dialogOpen = ref(false);

const form = useForm({
    type: 'ADJUSTMENT_IN',
    quantity: 1,
    reason: '',
});

const projectedStock = computed(() => {
    const quantity = Number(form.quantity) || 0;

    return form.type === 'ADJUSTMENT_IN' ? props.stock.current + quantity : props.stock.current - quantity;
});

function submit(): void {
    form.post(route('stock.adjust'), {
        preserveScroll: true,
        onSuccess: () => {
            dialogOpen.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Head title="Stock" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Stock" description="Pantau stok telur, inventory layer, dan pergerakan stok.">
                <template #actions>
                    <Button @click="dialogOpen = true">
                        <Plus class="size-4" />
                        Penyesuaian Stok
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Stok Telur"
                    :value="`${formatNumber(stock.current)} butir`"
                    :hint="`Minimum: ${formatNumber(stock.minimum_stock)} butir`"
                    :tone="isLowStock ? 'danger' : 'success'"
                    :icon="Boxes"
                />
                <StatCard title="Nilai Persediaan" :value="formatRupiah(stock.inventory_value)" :icon="Wallet" />
                <StatCard title="Jumlah Batch" :value="formatNumber(layers.total)" hint="Inventory layer tersimpan" :icon="Layers" />
                <StatCard
                    title="Konsistensi Stok"
                    :value="stock.consistent ? 'Konsisten' : 'Tidak konsisten'"
                    :tone="stock.consistent ? 'success' : 'danger'"
                    :icon="stock.consistent ? CircleCheck : AlertTriangle"
                />
            </div>

            <div
                v-if="isLowStock"
                class="flex items-start gap-3 rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-4 text-sm text-brand-danger"
            >
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                <p>Stok telur tersisa {{ formatNumber(stock.current) }} butir, di bawah/atau sama dengan batas minimum.</p>
            </div>

            <div
                v-if="!stock.consistent"
                class="flex items-start gap-3 rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-4 text-sm text-brand-danger"
            >
                <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                <p>
                    Peringatan: total pergerakan stok ({{ formatNumber(stock.current) }}) tidak sama dengan sisa inventory layer ({{
                        formatNumber(stock.layers_remaining)
                    }}). Periksa data stok Anda.
                </p>
            </div>

            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-foreground">Inventory layer (FIFO)</h2>
                <DataTable
                    :columns="layerColumns"
                    :rows="layers.data"
                    empty-title="Belum ada inventory layer"
                    empty-description="Layer dibuat otomatis saat Anda mencatat pembelian."
                >
                    <template #cell-purchase_number="{ row }">
                        <span class="font-medium text-foreground">{{ row.purchase_number ?? '-' }}</span>
                    </template>
                </DataTable>
                <Pagination :links="layers.links" :from="layers.from" :to="layers.to" :total="layers.total" />
            </div>

            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-foreground">Riwayat pergerakan stok</h2>
                <DataTable :columns="movementColumns" :rows="movements.data" empty-title="Belum ada pergerakan stok">
                    <template #cell-quantity="{ row }">
                        <span :class="row.quantity >= 0 ? 'text-brand-green-dark' : 'text-brand-danger'">
                            {{ row.quantity > 0 ? '+' : '' }}{{ formatNumber(row.quantity) }}
                        </span>
                    </template>
                </DataTable>
                <Pagination :links="movements.links" :from="movements.from" :to="movements.to" :total="movements.total" />
            </div>

            <Dialog v-model:open="dialogOpen">
                <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
                    <DialogHeader>
                        <DialogTitle>Penyesuaian stok</DialogTitle>
                        <DialogDescription>
                            Gunakan untuk koreksi stok fisik (misalnya telur pecah). Histori pembelian dan penjualan tidak berubah.
                        </DialogDescription>
                    </DialogHeader>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div class="grid gap-2">
                            <Label for="type">Jenis</Label>
                            <Select id="type" v-model="form.type">
                                <option value="ADJUSTMENT_IN">Masuk (tambah stok)</option>
                                <option value="ADJUSTMENT_OUT">Keluar (kurangi stok)</option>
                            </Select>
                            <InputError :message="form.errors.type" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="quantity">Jumlah (butir)</Label>
                            <Input id="quantity" v-model="form.quantity" type="number" min="1" inputmode="numeric" required />
                            <InputError :message="form.errors.quantity" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="reason">Alasan</Label>
                            <Textarea id="reason" v-model="form.reason" rows="2" required placeholder="Contoh: telur pecah saat pengiriman" />
                            <InputError :message="form.errors.reason" />
                        </div>

                        <p class="rounded-md bg-muted/60 px-3 py-2 text-sm text-muted-foreground">
                            Stok setelah penyesuaian:
                            <span class="font-medium text-foreground">{{ formatNumber(projectedStock) }} butir</span>. Stok masuk memakai HPP layer
                            terakhir, stok keluar memakai FIFO.
                        </p>

                        <DialogFooter class="[&>*]:w-full sm:[&>*]:w-auto">
                            <Button type="button" variant="outline" @click="dialogOpen = false">Batal</Button>
                            <Button type="submit" :disabled="form.processing">Simpan</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </PageContainer>
    </AppLayout>
</template>
