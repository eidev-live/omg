<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import StatCard from '@/components/StatCard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type SaleRow = {
    id: number;
    invoice_number: string;
    sale_date: string;
    customer_name: string | null;
    total_amount: number;
    paid_amount: number;
    outstanding: number;
    payment_status: string;
    delivery_status: string;
    status: string;
};

const props = defineProps<{
    sales: Paginator<SaleRow>;
    summary: { revenue: number; paid: number; outstanding: number; count: number };
    filters: { date_from: string | null; date_to: string | null; customer_id: number | null; payment: string | null; delivery: string | null };
    customers: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Laporan Penjualan', href: '/reports/sales' }];

const columns: DataTableColumn[] = [
    { key: 'invoice_number', label: 'Invoice', primary: true },
    { key: 'sale_date', label: 'Tanggal', type: 'date' },
    { key: 'customer_name', label: 'Customer' },
    { key: 'total_amount', label: 'Pendapatan', type: 'currency', align: 'right' },
    { key: 'paid_amount', label: 'Dibayar', type: 'currency', align: 'right' },
    { key: 'outstanding', label: 'Sisa', type: 'currency', align: 'right' },
    { key: 'payment_status', label: 'Status' },
];

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');
const payment = ref(props.filters.payment ?? '');
const delivery = ref(props.filters.delivery ?? '');

const query = computed(() => ({
    date_from: dateFrom.value,
    date_to: dateTo.value,
    customer_id: customerId.value,
    payment: payment.value,
    delivery: delivery.value,
}));

function apply(): void {
    router.get(route('reports.sales'), query.value, { preserveState: true, replace: true, preserveScroll: true });
}

const exportUrl = computed(() => route('reports.sales.export', query.value));
</script>

<template>
    <Head title="Laporan Penjualan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Laporan Penjualan" description="Rekap transaksi penjualan berdasarkan periode dan status.">
                <template #actions>
                    <Button variant="outline" as-child>
                        <a :href="exportUrl">
                            <Download class="size-4" />
                            Ekspor CSV
                        </a>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard title="Pendapatan" :value="formatRupiah(summary.revenue)" />
                <StatCard title="Dibayar" :value="formatRupiah(summary.paid)" tone="success" />
                <StatCard title="Sisa (piutang)" :value="formatRupiah(summary.outstanding)" :tone="summary.outstanding > 0 ? 'danger' : 'default'" />
                <StatCard title="Jumlah Transaksi" :value="formatNumber(summary.count)" />
            </div>

            <div class="grid gap-3 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <div class="grid gap-2">
                    <Label for="date_from" class="text-xs text-muted-foreground">Dari</Label>
                    <Input id="date_from" v-model="dateFrom" type="date" />
                </div>
                <div class="grid gap-2">
                    <Label for="date_to" class="text-xs text-muted-foreground">Sampai</Label>
                    <Input id="date_to" v-model="dateTo" type="date" />
                </div>
                <div class="grid gap-2">
                    <Label for="customer" class="text-xs text-muted-foreground">Customer</Label>
                    <Select id="customer" v-model="customerId">
                        <option value="">Semua</option>
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                    </Select>
                </div>
                <div class="grid gap-2">
                    <Label for="payment" class="text-xs text-muted-foreground">Pembayaran</Label>
                    <Select id="payment" v-model="payment">
                        <option value="">Semua</option>
                        <option value="UNPAID">Belum Lunas</option>
                        <option value="PARTIAL">Sebagian</option>
                        <option value="PAID">Lunas</option>
                    </Select>
                </div>
                <div class="grid gap-2">
                    <Label for="delivery" class="text-xs text-muted-foreground">Pengiriman</Label>
                    <Select id="delivery" v-model="delivery">
                        <option value="">Semua</option>
                        <option value="PENDING">Menunggu</option>
                        <option value="SHIPPED">Dikirim</option>
                        <option value="DELIVERED">Terkirim</option>
                    </Select>
                </div>
                <div class="lg:col-span-5">
                    <Button @click="apply">Terapkan Filter</Button>
                </div>
            </div>

            <DataTable :columns="columns" :rows="sales.data" empty-title="Tidak ada data penjualan" empty-description="Coba ubah filter periode.">
                <template #cell-payment_status="{ row }">
                    <div class="flex flex-wrap justify-end gap-1 md:justify-start">
                        <StatusBadge :status="row.payment_status" />
                    </div>
                </template>
            </DataTable>

            <Pagination :links="sales.links" :from="sales.from" :to="sales.to" :total="sales.total" />
        </PageContainer>
    </AppLayout>
</template>
