<script setup lang="ts">
import CustomerForm from '@/components/CustomerForm.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface CustomerData {
    id: number;
    name: string;
    phone: string | null;
    location: string | null;
    notes: string | null;
    is_active: boolean;
}

defineProps<{
    customer: CustomerData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customer', href: '/customers' },
    { title: 'Edit', href: '#' },
];
</script>

<template>
    <Head title="Edit Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Edit Customer" :description="customer.name" />

            <CustomerForm :customer="customer" />
        </PageContainer>
    </AppLayout>
</template>
