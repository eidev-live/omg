<script setup lang="ts">
import CustomerForm from '@/components/CustomerForm.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customer', href: '/customers' },
    { title: 'Tambah', href: '/customers/create' },
];
</script>

<template>
    <Head title="Tambah Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Tambah Customer" description="Lengkapi data pelanggan baru." />

            <CustomerForm />
        </PageContainer>
    </AppLayout>
</template>
