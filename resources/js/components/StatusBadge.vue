<script setup lang="ts">
import { Badge, type BadgeVariants } from '@/components/ui/badge';
import { Ban, CheckCircle2, CircleDollarSign, Clock, PackageCheck, Truck } from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';

const props = defineProps<{
    status?: string | null;
}>();

interface StatusMeta {
    label: string;
    variant: BadgeVariants['variant'];
    icon: Component;
}

const statuses: Record<string, StatusMeta> = {
    UNPAID: { label: 'Belum Lunas', variant: 'warning', icon: CircleDollarSign },
    PARTIAL: { label: 'Sebagian', variant: 'warning', icon: CircleDollarSign },
    PAID: { label: 'Lunas', variant: 'success', icon: CheckCircle2 },
    PENDING: { label: 'Menunggu', variant: 'muted', icon: Clock },
    SHIPPED: { label: 'Dikirim', variant: 'default', icon: Truck },
    DELIVERED: { label: 'Terkirim', variant: 'success', icon: PackageCheck },
    ACTIVE: { label: 'Aktif', variant: 'success', icon: CheckCircle2 },
    CANCELLED: { label: 'Dibatalkan', variant: 'danger', icon: Ban },
};

const meta = computed<StatusMeta>(() => statuses[props.status ?? ''] ?? { label: props.status ?? '-', variant: 'muted', icon: Clock });
</script>

<template>
    <Badge :variant="meta.variant">
        <component :is="meta.icon" class="size-3.5" />
        {{ meta.label }}
    </Badge>
</template>
