<script setup lang="ts">
import EmptyState from '@/components/EmptyState.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import TrendChart from '@/components/TrendChart.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, formatRupiah, formatShortDate } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { AlertTriangle, BarChart3, Boxes, PackagePlus, ShoppingCart, TrendingUp, Wallet } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Summary {
    revenue: number;
    sales_count: number;
    cash_in: number;
    outstanding: number;
    outstanding_count: number;
    purchases_total: number;
    purchases_count: number;
    cogs: number;
    gross_profit: number;
    margin: number;
    stock: number;
    minimum_stock: number;
    low_stock: boolean;
    pending_delivery_count: number;
}

const props = defineProps<{
    period: string;
    dateFrom: string;
    dateTo: string;
    summary: Summary;
    salesTrend: { date: string; revenue: number }[];
    profitTrend: { date: string; gross_profit: number }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

const periods = [
    { key: 'today', label: 'Hari Ini' },
    { key: 'week', label: 'Minggu Ini' },
    { key: 'month', label: 'Bulan Ini' },
    { key: 'year', label: 'Tahun Ini' },
    { key: 'custom', label: 'Custom' },
];

const customFrom = ref(props.dateFrom);
const customTo = ref(props.dateTo);

watch(
    () => [props.dateFrom, props.dateTo],
    ([from, to]) => {
        customFrom.value = from;
        customTo.value = to;
    },
);

function apply(nextPeriod: string, from = customFrom.value, to = customTo.value): void {
    router.get(
        route('dashboard'),
        { period: nextPeriod, date_from: from, date_to: to },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}

const salesLabels = computed(() => props.salesTrend.map((point) => formatShortDate(point.date)));
const salesValues = computed(() => props.salesTrend.map((point) => point.revenue));
const profitLabels = computed(() => props.profitTrend.map((point) => formatShortDate(point.date)));
const profitValues = computed(() => props.profitTrend.map((point) => point.gross_profit));
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Dashboard" description="Ringkasan bisnis telur Anda." />

            <div class="space-y-3 rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex flex-wrap gap-2">
                    <Button
                        v-for="option in periods"
                        :key="option.key"
                        :variant="period === option.key ? 'default' : 'outline'"
                        size="sm"
                        @click="apply(option.key)"
                    >
                        {{ option.label }}
                    </Button>
                </div>

                <div v-if="period === 'custom'" class="grid gap-3 sm:grid-cols-[1fr_1fr_auto] sm:items-end">
                    <div class="grid gap-2">
                        <Label for="date_from" class="text-xs text-muted-foreground">Dari tanggal</Label>
                        <Input id="date_from" v-model="customFrom" type="date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="date_to" class="text-xs text-muted-foreground">Sampai tanggal</Label>
                        <Input id="date_to" v-model="customTo" type="date" />
                    </div>
                    <Button variant="secondary" @click="apply('custom')">Terapkan</Button>
                </div>
                <p v-else class="text-xs text-muted-foreground">
                    Periode: {{ formatShortDate(props.dateFrom) }} &ndash; {{ formatShortDate(props.dateTo) }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Total Penjualan"
                    :value="formatRupiah(summary.revenue)"
                    :hint="`${formatNumber(summary.sales_count)} transaksi`"
                    :icon="ShoppingCart"
                />
                <StatCard title="Uang Masuk" :value="formatRupiah(summary.cash_in)" tone="success" :icon="Wallet" />
                <StatCard
                    title="Piutang"
                    :value="formatRupiah(summary.outstanding)"
                    :hint="`${formatNumber(summary.outstanding_count)} transaksi belum lunas`"
                    :tone="summary.outstanding > 0 ? 'danger' : 'default'"
                />
                <StatCard
                    title="Total Pembelian"
                    :value="formatRupiah(summary.purchases_total)"
                    :hint="`${formatNumber(summary.purchases_count)} pembelian`"
                    :icon="PackagePlus"
                />
                <StatCard title="HPP / COGS" :value="formatRupiah(summary.cogs)" />
                <StatCard
                    title="Gross Profit"
                    :value="formatRupiah(summary.gross_profit)"
                    :hint="`Margin ${summary.margin}%`"
                    tone="success"
                    :icon="TrendingUp"
                />
                <StatCard
                    title="Stock Telur"
                    :value="`${formatNumber(summary.stock)} butir`"
                    :hint="`Minimum ${formatNumber(summary.minimum_stock)} butir`"
                    :tone="summary.low_stock ? 'danger' : 'success'"
                    :icon="Boxes"
                />
            </div>

            <div class="grid gap-3">
                <div
                    v-if="summary.outstanding_count > 0"
                    class="flex items-start gap-3 rounded-lg border border-brand-yellow/40 bg-brand-yellow/15 p-4 text-sm text-[#8A6400]"
                >
                    <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                    <p>{{ formatNumber(summary.outstanding_count) }} transaksi belum lunas dengan total {{ formatRupiah(summary.outstanding) }}.</p>
                </div>
                <div
                    v-if="summary.pending_delivery_count > 0"
                    class="flex items-start gap-3 rounded-lg border border-border bg-muted p-4 text-sm text-foreground"
                >
                    <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                    <p>{{ formatNumber(summary.pending_delivery_count) }} transaksi belum dikirim.</p>
                </div>
                <div
                    v-if="summary.low_stock"
                    class="flex items-start gap-3 rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-4 text-sm text-brand-danger"
                >
                    <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                    <p>Stock telur tersisa {{ formatNumber(summary.stock) }} butir, di bawah/atau sama dengan batas minimum.</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-3 rounded-xl border border-border bg-card p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-foreground">Tren Penjualan</h2>
                    <TrendChart v-if="salesValues.length > 0" :labels="salesLabels" :data="salesValues" color="#F9844A" />
                    <EmptyState
                        v-else
                        title="Belum ada penjualan"
                        description="Grafik muncul setelah ada transaksi pada periode ini."
                        :icon="BarChart3"
                    />
                </div>
                <div class="space-y-3 rounded-xl border border-border bg-card p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-foreground">Tren Laba Kotor</h2>
                    <TrendChart v-if="profitValues.length > 0" :labels="profitLabels" :data="profitValues" color="#43AA8B" />
                    <EmptyState v-else title="Belum ada laba" description="Grafik muncul setelah ada transaksi pada periode ini." :icon="BarChart3" />
                </div>
            </div>
        </PageContainer>
    </AppLayout>
</template>
