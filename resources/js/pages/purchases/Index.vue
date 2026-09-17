<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Plus } from 'lucide-vue-next';
import { onUnmounted, ref, watch } from 'vue';

type PurchaseRow = {
    id: number;
    purchase_number: string;
    purchase_date: string;
    quantity: number;
    egg_quantity: number;
    total_cost: number;
    cost_per_egg: number;
    status: string;
};

const props = defineProps<{
    purchases: Paginator<PurchaseRow>;
    filters: { search: string; status: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pembelian', href: '/purchases' }];

const columns: DataTableColumn[] = [
    { key: 'purchase_number', label: 'No. Pembelian', primary: true },
    { key: 'purchase_date', label: 'Tanggal', type: 'date' },
    { key: 'quantity', label: 'Jumlah', type: 'number', align: 'right' },
    { key: 'egg_quantity', label: 'Total Telur', type: 'number', align: 'right' },
    { key: 'total_cost', label: 'Total Biaya', type: 'currency', align: 'right' },
    { key: 'cost_per_egg', label: 'HPP / Butir', type: 'currency', align: 'right' },
    { key: 'status', label: 'Status' },
];

const search = ref(props.filters.search);
const status = ref(props.filters.status);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

function applyFilters(): void {
    router.get(
        route('purchases.index'),
        { search: search.value, status: status.value },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch(status, applyFilters);

onUnmounted(() => clearTimeout(searchTimeout));
</script>

<template>
    <Head title="Pembelian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Pembelian" description="Catat telur masuk dan pantau HPP per butir.">
                <template #actions>
                    <Button as-child>
                        <Link :href="route('purchases.create')">
                            <Plus class="size-4" />
                            Tambah Pembelian
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-3 sm:grid-cols-[1fr_12rem]">
                <Input v-model="search" type="search" placeholder="Cari nomor pembelian atau catatan..." />
                <Select v-model="status">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="cancelled">Dibatalkan</option>
                </Select>
            </div>

            <DataTable
                :columns="columns"
                :rows="purchases.data"
                empty-title="Belum ada pembelian"
                empty-description="Catat pembelian pertama Anda agar stok telur bertambah."
            >
                <template #empty-action>
                    <Button as-child>
                        <Link :href="route('purchases.create')">
                            <Plus class="size-4" />
                            Tambah Pembelian
                        </Link>
                    </Button>
                </template>

                <template #cell-purchase_number="{ row }">
                    <Link :href="route('purchases.show', { purchase: row.id })" class="font-medium text-primary hover:underline">
                        {{ row.purchase_number }}
                    </Link>
                </template>

                <template #cell-status="{ row }">
                    <StatusBadge :status="row.status" />
                </template>

                <template #actions="{ row }">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="route('purchases.show', { purchase: row.id })" :aria-label="`Lihat ${row.purchase_number}`">
                            <Eye class="size-4" />
                        </Link>
                    </Button>
                </template>
            </DataTable>

            <Pagination :links="purchases.links" :from="purchases.from" :to="purchases.to" :total="purchases.total" />
        </PageContainer>
    </AppLayout>
</template>
