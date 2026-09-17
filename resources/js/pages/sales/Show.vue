<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatDate, formatDateTime, formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Ban } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type SaleItem = {
    id: number;
    sale_type_name: string;
    quantity: number;
    egg_quantity: number;
    unit_price: number;
    subtotal: number;
    unit_cost: number;
    total_cost: number;
    profit: number;
};

type Movement = {
    id: number;
    movement_date: string;
    movement_type: string;
    movement_label: string;
    quantity: number;
    notes: string | null;
};

type SaleDetail = {
    id: number;
    invoice_number: string;
    sale_date: string;
    customer: string | null;
    customer_phone: string | null;
    total_amount: number;
    paid_amount: number;
    outstanding: number;
    payment_status: string;
    payment_date: string | null;
    delivery_status: string;
    delivered_at: string | null;
    notes: string | null;
    status: string;
    cancelled_at: string | null;
    cancel_reason: string | null;
    created_by: string | null;
    total_eggs: number;
    revenue: number;
    cogs: number;
    profit: number;
    margin: number;
    items: SaleItem[];
    movements: Movement[];
};

const props = defineProps<{
    sale: SaleDetail;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Penjualan', href: '/sales' },
    { title: props.sale.invoice_number, href: '#' },
];

const itemColumns: DataTableColumn[] = [
    { key: 'sale_type_name', label: 'Item', primary: true },
    { key: 'quantity', label: 'Qty', type: 'number', align: 'right' },
    { key: 'egg_quantity', label: 'Telur', type: 'number', align: 'right' },
    { key: 'unit_price', label: 'Harga', type: 'currency', align: 'right' },
    { key: 'subtotal', label: 'Subtotal', type: 'currency', align: 'right' },
    { key: 'total_cost', label: 'HPP', type: 'currency', align: 'right' },
    { key: 'profit', label: 'Laba', type: 'currency', align: 'right' },
];

const movementColumns: DataTableColumn[] = [
    { key: 'movement_label', label: 'Jenis', primary: true },
    { key: 'movement_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity', label: 'Jumlah', type: 'number', align: 'right' },
    { key: 'notes', label: 'Catatan' },
];

const isCancelled = computed(() => props.sale.status === 'CANCELLED');

const dialogOpen = ref(false);
const reason = ref('');
const processing = ref(false);

function submitCancel(): void {
    processing.value = true;

    router.patch(
        route('sales.cancel', { sale: props.sale.id }),
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

const paymentDialogOpen = ref(false);
const paymentForm = useForm({
    paid_amount: props.sale.paid_amount,
    payment_date: props.sale.payment_date ?? '',
});

function submitPayment(): void {
    paymentForm.patch(route('sales.payment', { sale: props.sale.id }), {
        preserveScroll: true,
        onSuccess: () => {
            paymentDialogOpen.value = false;
        },
    });
}

const deliveryDialogOpen = ref(false);
const deliveryForm = useForm({
    delivery_status: props.sale.delivery_status,
});

function submitDelivery(): void {
    deliveryForm.patch(route('sales.delivery', { sale: props.sale.id }), {
        preserveScroll: true,
        onSuccess: () => {
            deliveryDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`Penjualan ${sale.invoice_number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader :title="sale.invoice_number" :description="`Dibuat ${formatDate(sale.sale_date)}`">
                <template #actions>
                    <StatusBadge :status="sale.payment_status" />
                    <StatusBadge :status="sale.delivery_status" />
                    <Button v-if="!isCancelled" variant="outline" @click="paymentDialogOpen = true">Pembayaran</Button>
                    <Button v-if="!isCancelled" variant="outline" @click="deliveryDialogOpen = true">Pengiriman</Button>
                    <Button v-if="!isCancelled" variant="destructive" @click="dialogOpen = true">
                        <Ban class="size-4" />
                        Batalkan
                    </Button>
                </template>
            </PageHeader>

            <div v-if="isCancelled" class="rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-4 text-sm text-brand-danger">
                Transaksi dibatalkan{{ sale.cancelled_at ? ` pada ${formatDateTime(sale.cancelled_at)}` : '' }}.
                <span v-if="sale.cancel_reason">Alasan: {{ sale.cancel_reason }}</span>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 shadow-sm md:p-6">
                <dl class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-xs text-muted-foreground">Customer</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ sale.customer ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Telepon</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ sale.customer_phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Dicatat oleh</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ sale.created_by ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Total telur</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatNumber(sale.total_eggs) }} butir</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Tanggal pembayaran</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ sale.payment_date ? formatDate(sale.payment_date) : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Terkirim pada</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ sale.delivered_at ? formatDateTime(sale.delivered_at) : '-' }}</dd>
                    </div>
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-xs text-muted-foreground">Catatan</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ sale.notes ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-foreground">Item</h2>
                <DataTable :columns="itemColumns" :rows="sale.items" empty-title="Tidak ada item">
                    <template #cell-sale_type_name="{ row }">
                        <span class="font-medium text-foreground">{{ row.sale_type_name }}</span>
                    </template>
                    <template #cell-profit="{ row }">
                        <span :class="row.profit >= 0 ? 'text-brand-green-dark' : 'text-brand-danger'">{{ formatRupiah(row.profit) }}</span>
                    </template>
                </DataTable>
            </div>

            <div class="grid gap-4 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4 md:p-6">
                <div>
                    <p class="text-xs text-muted-foreground">Pendapatan</p>
                    <p class="mt-1 text-base font-semibold text-foreground">{{ formatRupiah(sale.revenue) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">HPP (COGS)</p>
                    <p class="mt-1 text-base font-semibold text-foreground">{{ formatRupiah(sale.cogs) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Laba Kotor</p>
                    <p class="mt-1 text-base font-semibold text-brand-green-dark">{{ formatRupiah(sale.profit) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Margin</p>
                    <p class="mt-1 text-base font-semibold text-foreground">{{ sale.margin }}%</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Dibayar</p>
                    <p class="mt-1 text-base font-semibold text-foreground">{{ formatRupiah(sale.paid_amount) }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Sisa (piutang)</p>
                    <p class="mt-1 text-base font-semibold" :class="sale.outstanding > 0 ? 'text-brand-danger' : 'text-foreground'">
                        {{ formatRupiah(sale.outstanding) }}
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-foreground">Pergerakan stok</h2>
                <DataTable :columns="movementColumns" :rows="sale.movements" empty-title="Belum ada pergerakan stok">
                    <template #cell-quantity="{ row }">
                        <span :class="row.quantity >= 0 ? 'text-brand-green-dark' : 'text-brand-danger'">
                            {{ row.quantity > 0 ? '+' : '' }}{{ formatNumber(row.quantity) }}
                        </span>
                    </template>
                </DataTable>
            </div>

            <Dialog v-model:open="paymentDialogOpen">
                <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
                    <DialogHeader>
                        <DialogTitle>Perbarui pembayaran</DialogTitle>
                        <DialogDescription>Total transaksi {{ formatRupiah(sale.total_amount) }}.</DialogDescription>
                    </DialogHeader>

                    <form class="space-y-4" @submit.prevent="submitPayment">
                        <div class="grid gap-2">
                            <Label for="paid_amount">Jumlah dibayar (Rp)</Label>
                            <Input id="paid_amount" v-model="paymentForm.paid_amount" type="number" min="0" step="500" inputmode="numeric" />
                            <InputError :message="paymentForm.errors.paid_amount" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="payment_date">Tanggal pembayaran</Label>
                            <Input id="payment_date" v-model="paymentForm.payment_date" type="date" />
                            <InputError :message="paymentForm.errors.payment_date" />
                        </div>

                        <DialogFooter class="[&>*]:w-full sm:[&>*]:w-auto">
                            <Button type="button" variant="outline" @click="paymentDialogOpen = false">Batal</Button>
                            <Button type="submit" :disabled="paymentForm.processing">Simpan</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="deliveryDialogOpen">
                <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
                    <DialogHeader>
                        <DialogTitle>Perbarui pengiriman</DialogTitle>
                        <DialogDescription>Ubah status pengiriman transaksi {{ sale.invoice_number }}.</DialogDescription>
                    </DialogHeader>

                    <form class="space-y-4" @submit.prevent="submitDelivery">
                        <div class="grid gap-2">
                            <Label for="delivery_status">Status pengiriman</Label>
                            <Select id="delivery_status" v-model="deliveryForm.delivery_status">
                                <option value="PENDING">Menunggu</option>
                                <option value="SHIPPED">Dikirim</option>
                                <option value="DELIVERED">Terkirim</option>
                            </Select>
                            <InputError :message="deliveryForm.errors.delivery_status" />
                        </div>

                        <DialogFooter class="[&>*]:w-full sm:[&>*]:w-auto">
                            <Button type="button" variant="outline" @click="deliveryDialogOpen = false">Batal</Button>
                            <Button type="submit" :disabled="deliveryForm.processing">Simpan</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog v-model:open="dialogOpen">
                <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
                    <DialogHeader>
                        <DialogTitle>Batalkan transaksi?</DialogTitle>
                        <DialogDescription>
                            Transaksi {{ sale.invoice_number }} ({{ formatRupiah(sale.total_amount) }}) akan dibatalkan dan stok
                            sebanyak {{ formatNumber(sale.total_eggs) }} butir dikembalikan.
                        </DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="cancel_reason">Alasan (opsional)</Label>
                        <Textarea id="cancel_reason" v-model="reason" rows="2" placeholder="Contoh: customer membatalkan pesanan" />
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
