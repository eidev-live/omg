<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Plus } from 'lucide-vue-next';
import { onUnmounted, ref, watch } from 'vue';

type SaleRow = {
    id: number;
    invoice_number: string;
    sale_date: string;
    customer_name: string | null;
    total_amount: number;
    paid_amount: number;
    payment_status: string;
    delivery_status: string;
    status: string;
};

const props = defineProps<{
    sales: Paginator<SaleRow>;
    filters: { search: string; payment: string; delivery: string; date_from: string; date_to: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Penjualan', href: '/sales' }];

const columns: DataTableColumn[] = [
    { key: 'invoice_number', label: 'Invoice', primary: true },
    { key: 'sale_date', label: 'Tanggal', type: 'date' },
    { key: 'customer_name', label: 'Customer' },
    { key: 'total_amount', label: 'Total', type: 'currency', align: 'right' },
    { key: 'payment_status', label: 'Pembayaran' },
    { key: 'delivery_status', label: 'Pengiriman' },
];

const search = ref(props.filters.search);
const payment = ref(props.filters.payment);
const delivery = ref(props.filters.delivery);
const dateFrom = ref(props.filters.date_from);
const dateTo = ref(props.filters.date_to);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

function applyFilters(): void {
    router.get(
        route('sales.index'),
        {
            search: search.value,
            payment: payment.value,
            delivery: delivery.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch([payment, delivery, dateFrom, dateTo], applyFilters);

onUnmounted(() => clearTimeout(searchTimeout));
</script>

<template>
    <Head title="Penjualan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Penjualan" description="Catat dan pantau transaksi penjualan telur.">
                <template #actions>
                    <Button as-child>
                        <Link :href="route('sales.create')">
                            <Plus class="size-4" />
                            Transaksi Penjualan
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="grid gap-2 lg:col-span-2">
                    <Label for="search" class="text-xs text-muted-foreground">Cari</Label>
                    <Input id="search" v-model="search" type="search" placeholder="Invoice atau nama customer..." />
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
                <div class="grid gap-2">
                    <Label for="date_from" class="text-xs text-muted-foreground">Dari tanggal</Label>
                    <Input id="date_from" v-model="dateFrom" type="date" />
                </div>
                <div class="grid gap-2">
                    <Label for="date_to" class="text-xs text-muted-foreground">Sampai tanggal</Label>
                    <Input id="date_to" v-model="dateTo" type="date" />
                </div>
            </div>

            <DataTable
                :columns="columns"
                :rows="sales.data"
                empty-title="Belum ada transaksi penjualan"
                empty-description="Mulai catat penjualan pertama Anda."
            >
                <template #empty-action>
                    <Button as-child>
                        <Link :href="route('sales.create')">
                            <Plus class="size-4" />
                            Transaksi Penjualan
                        </Link>
                    </Button>
                </template>

                <template #cell-invoice_number="{ row }">
                    <div class="flex items-center gap-2">
                        <Link :href="route('sales.show', { sale: row.id })" class="font-medium text-primary hover:underline">
                            {{ row.invoice_number }}
                        </Link>
                        <Badge v-if="row.status === 'CANCELLED'" variant="danger">Dibatalkan</Badge>
                    </div>
                </template>

                <template #cell-payment_status="{ row }">
                    <StatusBadge :status="row.payment_status" />
                </template>

                <template #cell-delivery_status="{ row }">
                    <StatusBadge :status="row.delivery_status" />
                </template>

                <template #actions="{ row }">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('sales.show', { sale: row.id })" :aria-label="`Lihat ${row.invoice_number}`">
                            <Eye class="size-4" />
                        </Link>
                    </Button>
                </template>
            </DataTable>

            <Pagination :links="sales.links" :from="sales.from" :to="sales.to" :total="sales.total" />
        </PageContainer>
    </AppLayout>
</template>
