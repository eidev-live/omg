<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import StatCard from '@/components/StatCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type PurchaseRow = {
    id: number;
    purchase_number: string;
    purchase_date: string;
    quantity: number;
    egg_quantity: number;
    total_cost: number;
    cost_per_egg: number;
};

const props = defineProps<{
    purchases: Paginator<PurchaseRow>;
    summary: { total_cost: number; egg_quantity: number; count: number };
    filters: { date_from: string | null; date_to: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Laporan Pembelian', href: '/reports/purchases' }];

const columns: DataTableColumn[] = [
    { key: 'purchase_number', label: 'No. Pembelian', primary: true },
    { key: 'purchase_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity', label: 'Jumlah Ikat', type: 'number', align: 'right' },
    { key: 'egg_quantity', label: 'Total Telur', type: 'number', align: 'right' },
    { key: 'total_cost', label: 'Total Biaya', type: 'currency', align: 'right' },
    { key: 'cost_per_egg', label: 'HPP / Butir', type: 'currency', align: 'right' },
];

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

const query = computed(() => ({ date_from: dateFrom.value, date_to: dateTo.value }));

function apply(): void {
    router.get(route('reports.purchases'), query.value, { preserveState: true, replace: true, preserveScroll: true });
}

const exportUrl = computed(() => route('reports.purchases.export', query.value));
</script>

<template>
    <Head title="Laporan Pembelian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Laporan Pembelian" description="Rekap pembelian telur dan HPP per butir.">
                <template #actions>
                    <Button variant="outline" as-child>
                        <a :href="exportUrl">
                            <Download class="size-4" />
                            Ekspor CSV
                        </a>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-3">
                <StatCard title="Total Biaya" :value="formatRupiah(summary.total_cost)" />
                <StatCard title="Total Telur" :value="`${formatNumber(summary.egg_quantity)} butir`" />
                <StatCard title="Jumlah Pembelian" :value="formatNumber(summary.count)" />
            </div>

            <div class="grid gap-3 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                <div class="grid gap-2">
                    <Label for="date_from" class="text-xs text-muted-foreground">Dari</Label>
                    <Input id="date_from" v-model="dateFrom" type="date" />
                </div>
                <div class="grid gap-2">
                    <Label for="date_to" class="text-xs text-muted-foreground">Sampai</Label>
                    <Input id="date_to" v-model="dateTo" type="date" />
                </div>
                <Button @click="apply">Terapkan Filter</Button>
            </div>

            <DataTable :columns="columns" :rows="purchases.data" empty-title="Tidak ada data pembelian" empty-description="Coba ubah filter periode." />

            <Pagination :links="purchases.links" :from="purchases.from" :to="purchases.to" :total="purchases.total" />
        </PageContainer>
    </AppLayout>
</template>
