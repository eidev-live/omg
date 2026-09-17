<script setup lang="ts">
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import SaleTypeForm from '@/components/SaleTypeForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface SaleTypeData {
    id: number;
    name: string;
    egg_quantity: number;
    selling_price: number;
    is_active: boolean;
}

defineProps<{
    saleType: SaleTypeData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tipe Penjualan', href: '/sale-types' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Tipe Penjualan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Edit Tipe Penjualan" :description="saleType.name" />

            <SaleTypeForm :sale-type="saleType" />
        </PageContainer>
    </AppLayout>
</template>
