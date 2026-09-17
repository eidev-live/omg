<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, LoaderCircle, Plus, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';

interface CustomerOption {
    id: number;
    name: string;
}

interface SaleTypeOption {
    id: number;
    name: string;
    egg_quantity: number;
    selling_price: number;
}

const props = defineProps<{
    customers: CustomerOption[];
    saleTypes: SaleTypeOption[];
    stockAvailable: number;
    today: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Penjualan', href: '/sales' },
    { title: 'Transaksi', href: '/sales/create' },
];

let rowCounter = 0;

function newRow() {
    return { key: ++rowCounter, sale_type_id: '', quantity: 1 };
}

const form = useForm({
    sale_date: props.today,
    customer_id: '',
    items: [newRow()],
    paid_amount: 0,
    payment_date: '',
    delivery_status: 'PENDING',
    notes: '',
});

function typeFor(id: string | number): SaleTypeOption | null {
    return props.saleTypes.find((type) => String(type.id) === String(id)) ?? null;
}

function addItem(): void {
    form.items.push(newRow());
}

function removeItem(index: number): void {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
}

function itemError(index: number, field: string): string | undefined {
    return (form.errors as Record<string, string | undefined>)[`items.${index}.${field}`];
}

const totalEggs = computed(() =>
    form.items.reduce((sum, item) => {
        const type = typeFor(item.sale_type_id);

        return type ? sum + type.egg_quantity * (Number(item.quantity) || 0) : sum;
    }, 0),
);

const totalAmount = computed(() =>
    form.items.reduce((sum, item) => {
        const type = typeFor(item.sale_type_id);

        return type ? sum + type.selling_price * (Number(item.quantity) || 0) : sum;
    }, 0),
);

const paidAmount = computed(() => Number(form.paid_amount) || 0);

const paymentPreview = computed(() => {
    if (paidAmount.value <= 0) {
        return 'UNPAID';
    }

    return paidAmount.value >= totalAmount.value ? 'PAID' : 'PARTIAL';
});

const stockShortage = computed(() => totalEggs.value > props.stockAvailable);

const submit = () => {
    form.post(route('sales.store'));
};
</script>

<template>
    <Head title="Transaksi Penjualan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Transaksi Penjualan" description="Pilih customer, item, lalu simpan. HPP dihitung otomatis dengan FIFO." />

            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-4 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-2 md:p-6">
                    <div class="grid gap-2">
                        <Label for="sale_date">Tanggal <span class="text-brand-danger">*</span></Label>
                        <Input id="sale_date" v-model="form.sale_date" type="date" required />
                        <InputError :message="form.errors.sale_date" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="customer_id">Customer <span class="text-brand-danger">*</span></Label>
                        <Select id="customer_id" v-model="form.customer_id">
                            <option value="" disabled>Pilih customer</option>
                            <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                        </Select>
                        <InputError :message="form.errors.customer_id" />
                    </div>
                </div>

                <div class="space-y-3 rounded-xl border border-border bg-card p-4 shadow-sm md:p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-foreground">Item penjualan</h2>
                        <Button type="button" variant="outline" size="sm" @click="addItem">
                            <Plus class="size-4" />
                            Tambah Item
                        </Button>
                    </div>

                    <InputError :message="form.errors.items" />

                    <div v-for="(item, index) in form.items" :key="item.key" class="grid gap-3 rounded-lg border border-border p-3 sm:grid-cols-12">
                        <div class="grid gap-2 sm:col-span-5">
                            <Label :for="`sale_type_${item.key}`" class="text-xs text-muted-foreground">Tipe</Label>
                            <Select :id="`sale_type_${item.key}`" v-model="item.sale_type_id">
                                <option value="" disabled>Pilih tipe</option>
                                <option v-for="type in saleTypes" :key="type.id" :value="type.id">
                                    {{ type.name }} ({{ formatNumber(type.egg_quantity) }} butir) &middot; {{ formatRupiah(type.selling_price) }}
                                </option>
                            </Select>
                            <InputError :message="itemError(index, 'sale_type_id')" />
                        </div>

                        <div class="grid gap-2 sm:col-span-3">
                            <Label :for="`quantity_${item.key}`" class="text-xs text-muted-foreground">Qty</Label>
                            <Input :id="`quantity_${item.key}`" v-model="item.quantity" type="number" min="1" inputmode="numeric" />
                            <InputError :message="itemError(index, 'quantity')" />
                        </div>

                        <div class="grid gap-2 sm:col-span-3">
                            <span class="text-xs text-muted-foreground">Subtotal</span>
                            <div class="flex h-10 items-center text-sm font-medium text-foreground">
                                {{
                                    typeFor(item.sale_type_id)
                                        ? formatRupiah(typeFor(item.sale_type_id)!.selling_price * (Number(item.quantity) || 0))
                                        : '-'
                                }}
                            </div>
                            <span class="text-xs text-muted-foreground">
                                {{
                                    typeFor(item.sale_type_id)
                                        ? `${formatNumber(typeFor(item.sale_type_id)!.egg_quantity * (Number(item.quantity) || 0))} butir`
                                        : ''
                                }}
                            </span>
                        </div>

                        <div class="flex items-end justify-end sm:col-span-1">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="text-brand-danger"
                                aria-label="Hapus item"
                                :disabled="form.items.length === 1"
                                @click="removeItem(index)"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-3 rounded-lg bg-muted/50 p-3 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-muted-foreground">Total telur</p>
                            <p class="text-sm font-semibold text-foreground">{{ formatNumber(totalEggs) }} butir</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Stok tersedia</p>
                            <p class="text-sm font-semibold text-foreground">{{ formatNumber(stockAvailable) }} butir</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Total harga</p>
                            <p class="text-sm font-semibold text-foreground">{{ formatRupiah(totalAmount) }}</p>
                        </div>
                    </div>

                    <div
                        v-if="stockShortage"
                        class="flex items-start gap-3 rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-3 text-sm text-brand-danger"
                    >
                        <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                        <p>
                            Stok telur tidak mencukupi. Stok tersedia {{ formatNumber(stockAvailable) }} butir, kebutuhan
                            {{ formatNumber(totalEggs) }} butir.
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 rounded-xl border border-border bg-card p-4 shadow-sm md:grid-cols-2 md:p-6">
                    <div class="grid gap-2">
                        <Label for="paid_amount">Jumlah dibayar (Rp)</Label>
                        <Input id="paid_amount" v-model="form.paid_amount" type="number" min="0" step="500" inputmode="numeric" />
                        <InputError :message="form.errors.paid_amount" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="payment_date">Tanggal pembayaran</Label>
                        <Input id="payment_date" v-model="form.payment_date" type="date" />
                        <InputError :message="form.errors.payment_date" />
                    </div>
                    <div class="grid gap-2">
                        <span class="text-sm font-medium">Status pembayaran</span>
                        <div><StatusBadge :status="paymentPreview" /></div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="delivery_status">Status pengiriman</Label>
                        <Select id="delivery_status" v-model="form.delivery_status">
                            <option value="PENDING">Menunggu</option>
                            <option value="SHIPPED">Dikirim</option>
                            <option value="DELIVERED">Terkirim</option>
                        </Select>
                        <InputError :message="form.errors.delivery_status" />
                    </div>
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="notes">Catatan</Label>
                        <Textarea id="notes" v-model="form.notes" rows="2" placeholder="Catatan tambahan (opsional)" />
                        <InputError :message="form.errors.notes" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button type="submit" :disabled="form.processing || stockShortage">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Simpan Transaksi
                    </Button>
                    <Button variant="outline" type="button" as-child>
                        <Link :href="route('sales.index')">Batal</Link>
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
