<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import EmptyState from '@/components/EmptyState.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import TrendChart from '@/components/TrendChart.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatRupiah, formatShortDate } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { BarChart3, Download } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type SeriesRow = {
    date: string;
    revenue: number;
    cogs: number;
    gross_profit: number;
};

const props = defineProps<{
    totals: { revenue: number; cogs: number; gross_profit: number; margin: number; sale_count: number };
    series: SeriesRow[];
    filters: { date_from: string | null; date_to: string | null; customer_id: number | null };
    customers: { id: number; name: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Laporan Profit', href: '/reports/profit' }];

const columns: DataTableColumn[] = [
    { key: 'date', label: 'Tanggal', type: 'date', primary: true },
    { key: 'revenue', label: 'Pendapatan', type: 'currency', align: 'right' },
    { key: 'cogs', label: 'COGS', type: 'currency', align: 'right' },
    { key: 'gross_profit', label: 'Laba Kotor', type: 'currency', align: 'right' },
    { key: 'margin', label: 'Margin', align: 'right' },
];

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const customerId = ref(props.filters.customer_id ? String(props.filters.customer_id) : '');

const query = computed(() => ({
    date_from: dateFrom.value,
    date_to: dateTo.value,
    customer_id: customerId.value,
}));

function apply(): void {
    router.get(route('reports.profit'), query.value, { preserveState: true, replace: true, preserveScroll: true });
}

const exportUrl = computed(() => route('reports.profit.export', query.value));

const labels = computed(() => props.series.map((row) => formatShortDate(row.date)));
const values = computed(() => props.series.map((row) => row.gross_profit));

function margin(row: SeriesRow): string {
    return `${row.revenue > 0 ? ((row.gross_profit / row.revenue) * 100).toFixed(2) : '0.00'}%`;
}
</script>

<template>
    <Head title="Laporan Profit" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Laporan Profit" description="Pendapatan, COGS, laba kotor, dan margin dari transaksi aktual.">
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
                <StatCard title="Pendapatan" :value="formatRupiah(totals.revenue)" />
                <StatCard title="HPP (COGS)" :value="formatRupiah(totals.cogs)" />
                <StatCard title="Laba Kotor" :value="formatRupiah(totals.gross_profit)" tone="success" />
                <StatCard title="Margin" :value="`${totals.margin}%`" />
            </div>

            <div class="grid gap-3 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end">
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
                <Button @click="apply">Terapkan Filter</Button>
            </div>

            <div class="space-y-3 rounded-xl border border-border bg-card p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-foreground">Tren Laba Kotor</h2>
                <TrendChart v-if="values.length > 0" :labels="labels" :data="values" color="#43AA8B" />
                <EmptyState v-else title="Belum ada laba" description="Tidak ada transaksi pada periode ini." :icon="BarChart3" />
            </div>

            <DataTable :columns="columns" :rows="series" row-key="date" empty-title="Tidak ada data profit" empty-description="Coba ubah filter periode.">
                <template #cell-gross_profit="{ row }">
                    <span class="text-brand-green-dark">{{ formatRupiah(row.gross_profit) }}</span>
                </template>
                <template #cell-margin="{ row }">
                    <span class="text-muted-foreground">{{ margin(row) }}</span>
                </template>
            </DataTable>
        </PageContainer>
    </AppLayout>
</template>
