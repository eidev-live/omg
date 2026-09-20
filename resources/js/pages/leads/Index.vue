<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type DataTableColumn, type Paginator } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Download } from 'lucide-vue-next';
import { computed, onUnmounted, ref, watch } from 'vue';

type LeadRow = {
    id: number;
    name: string;
    email: string;
    phone: string;
    created_at: string | null;
};

const props = defineProps<{
    leads: Paginator<LeadRow>;
    filters: { search: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Calon Pembeli', href: '/leads' }];

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Nama', primary: true },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'No. WhatsApp' },
    { key: 'created_at', label: 'Tanggal', type: 'datetime' },
];

const search = ref(props.filters.search);

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

function applyFilters(): void {
    router.get(route('leads.index'), { search: search.value }, { preserveState: true, replace: true, preserveScroll: true });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

onUnmounted(() => clearTimeout(searchTimeout));

const exportUrl = computed(() => route('leads.export', { search: search.value }));
</script>

<template>
    <Head title="Calon Pembeli" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Calon Pembeli" description="Data calon pembeli dari halaman utama.">
                <template #actions>
                    <Button variant="outline" as-child>
                        <a :href="exportUrl">
                            <Download class="size-4" />
                            Ekspor CSV
                        </a>
                    </Button>
                </template>
            </PageHeader>

            <Input v-model="search" type="search" placeholder="Cari nama, email, atau nomor WhatsApp..." />

            <DataTable
                :columns="columns"
                :rows="leads.data"
                empty-title="Belum ada calon pembeli"
                empty-description="Data akan muncul setelah pengunjung mengisi form di halaman utama."
            />

            <Pagination :links="leads.links" :from="leads.from" :to="leads.to" :total="leads.total" />
        </PageContainer>
    </AppLayout>
</template>
