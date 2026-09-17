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

type MovementRow = {
    id: number;
    movement_date: string;
    movement_type: string;
    movement_label: string;
    quantity: number;
    notes: string | null;
};

const props = defineProps<{
    summary: { current: number; inventory_value: number; stock_in: number; stock_out: number; adjustment: number };
    movements: Paginator<MovementRow>;
    filters: { date_from: string | null; date_to: string | null };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Laporan Stock', href: '/reports/stock' }];

const columns: DataTableColumn[] = [
    { key: 'movement_label', label: 'Jenis', primary: true },
    { key: 'movement_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity', label: 'Jumlah', type: 'number', align: 'right' },
    { key: 'notes', label: 'Catatan' },
];

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

const query = computed(() => ({ date_from: dateFrom.value, date_to: dateTo.value }));

function apply(): void {
    router.get(route('reports.stock'), query.value, { preserveState: true, replace: true, preserveScroll: true });
}

const exportUrl = computed(() => route('reports.stock.export', query.value));
</script>

<template>
    <Head title="Laporan Stock" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Laporan Stock" description="Stok terkini, arus masuk/keluar, penyesuaian, dan nilai persediaan.">
                <template #actions>
                    <Button variant="outline" as-child>
                        <a :href="exportUrl">
                            <Download class="size-4" />
                            Ekspor CSV
                        </a>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <StatCard title="Stok Saat Ini" :value="`${formatNumber(summary.current)} butir`" tone="success" />
                <StatCard title="Nilai Persediaan" :value="formatRupiah(summary.inventory_value)" />
                <StatCard title="Stok Masuk" :value="`${formatNumber(summary.stock_in)} butir`" />
                <StatCard title="Stok Keluar" :value="`${formatNumber(summary.stock_out)} butir`" tone="danger" />
                <StatCard
                    title="Penyesuaian"
                    :value="`${summary.adjustment > 0 ? '+' : ''}${formatNumber(summary.adjustment)} butir`"
                    :tone="summary.adjustment < 0 ? 'danger' : 'default'"
                />
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

            <DataTable :columns="columns" :rows="movements.data" empty-title="Tidak ada pergerakan stok" empty-description="Coba ubah filter periode.">
                <template #cell-quantity="{ row }">
                    <span :class="row.quantity >= 0 ? 'text-brand-green-dark' : 'text-brand-danger'">
                        {{ row.quantity > 0 ? '+' : '' }}{{ formatNumber(row.quantity) }}
                    </span>
                </template>
            </DataTable>

            <Pagination :links="movements.links" :from="movements.from" :to="movements.to" :total="movements.total" />
        </PageContainer>
    </AppLayout>
</template>
