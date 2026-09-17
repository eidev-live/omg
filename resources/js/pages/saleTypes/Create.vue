<script setup lang="ts">
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import SaleTypeForm from '@/components/SaleTypeForm.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tipe Penjualan', href: '/sale-types' },
    { title: 'Tambah', href: '/sale-types/create' },
];
</script>

<template>
    <Head title="Tambah Tipe Penjualan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Tambah Tipe Penjualan" description="Tentukan satuan dan harga jual." />

            <SaleTypeForm />
        </PageContainer>
    </AppLayout>
</template>
