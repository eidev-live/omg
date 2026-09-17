<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { useConfirm } from '@/composables/useConfirm';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber } from '@/lib/format';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { CircleCheck, CircleSlash, Pencil, Plus, Power, Trash2 } from 'lucide-vue-next';
import { onUnmounted, ref, watch } from 'vue';

type SaleTypeRow = {
    id: number;
    name: string;
    egg_quantity: number;
    selling_price: number;
    is_active: boolean;
};

const props = defineProps<{
    saleTypes: Paginator<SaleTypeRow>;
    filters: { search: string; status: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Tipe Penjualan', href: '/sale-types' }];

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Tipe', primary: true },
    { key: 'egg_quantity', label: 'Isi', type: 'number', align: 'right' },
    { key: 'selling_price', label: 'Harga Jual', type: 'currency', align: 'right' },
    { key: 'is_active', label: 'Status' },
];

const { confirm } = useConfirm();

const search = ref(props.filters.search);
const status = ref(props.filters.status);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

function applyFilters(): void {
    router.get(
        route('sale-types.index'),
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

async function toggleActive(row: SaleTypeRow): Promise<void> {
    const confirmed = await confirm({
        title: 'Ubah status tipe penjualan?',
        description: `${row.name} akan ${row.is_active ? 'dinonaktifkan' : 'diaktifkan'}.`,
        confirmLabel: 'Ya, Ubah',
    });

    if (confirmed) {
        router.patch(route('sale-types.toggle', { sale_type: row.id }), {}, { preserveScroll: true });
    }
}

async function destroy(row: SaleTypeRow): Promise<void> {
    const confirmed = await confirm({
        title: 'Hapus tipe penjualan?',
        description: `Tipe ${row.name} akan dihapus. Tipe yang sudah dipakai pada transaksi tidak dapat dihapus.`,
        confirmLabel: 'Ya, Hapus',
        destructive: true,
    });

    if (confirmed) {
        router.delete(route('sale-types.destroy', { sale_type: row.id }), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Tipe Penjualan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Tipe Penjualan" description="Atur satuan dan harga jual telur.">
                <template #actions>
                    <Button as-child>
                        <Link :href="route('sale-types.create')">
                            <Plus class="size-4" />
                            Tambah Tipe
                        </Link>
                    </Button>
                </template>
            </PageHeader>

            <div class="grid gap-3 sm:grid-cols-[1fr_12rem]">
                <Input v-model="search" type="search" placeholder="Cari nama tipe..." />
                <Select v-model="status">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </Select>
            </div>

            <DataTable
                :columns="columns"
                :rows="saleTypes.data"
                empty-title="Belum ada tipe penjualan"
                empty-description="Tambahkan satuan jual seperti Pack, Tray, atau Ikat."
            >
                <template #empty-action>
                    <Button as-child>
                        <Link :href="route('sale-types.create')">
                            <Plus class="size-4" />
                            Tambah Tipe
                        </Link>
                    </Button>
                </template>

                <template #cell-name="{ row }">
                    <span class="font-medium text-foreground">{{ row.name }}</span>
                </template>

                <template #cell-egg_quantity="{ row }">
                    <span>{{ formatNumber(row.egg_quantity) }} butir</span>
                </template>

                <template #cell-is_active="{ row }">
                    <Badge :variant="row.is_active ? 'success' : 'muted'">
                        <CircleCheck v-if="row.is_active" class="size-3.5" />
                        <CircleSlash v-else class="size-3.5" />
                        {{ row.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </template>

                <template #actions="{ row }">
                    <div class="flex items-center gap-1">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="route('sale-types.edit', { sale_type: row.id })" :aria-label="`Edit ${row.name}`">
                                <Pencil class="size-4" />
                            </Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            :aria-label="row.is_active ? `Nonaktifkan ${row.name}` : `Aktifkan ${row.name}`"
                            @click="toggleActive(row)"
                        >
                            <Power class="size-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="text-brand-danger" :aria-label="`Hapus ${row.name}`" @click="destroy(row)">
                            <Trash2 class="size-4" />
                        </Button>
                    </div>
                </template>
            </DataTable>

            <Pagination :links="saleTypes.links" :from="saleTypes.from" :to="saleTypes.to" :total="saleTypes.total" />
        </PageContainer>
    </AppLayout>
</template>
