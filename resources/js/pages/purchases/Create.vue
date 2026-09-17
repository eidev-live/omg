<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber, formatRupiah } from '@/lib/format';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Pembelian', href: '/purchases' },
    { title: 'Tambah', href: '/purchases/create' },
];

const form = useForm({
    purchase_date: new Date().toISOString().slice(0, 10),
    quantity: 1,
    egg_per_unit: 180,
    total_cost: 0,
    notes: '',
});

const totalEggs = computed(() => (Number(form.quantity) || 0) * (Number(form.egg_per_unit) || 0));
const costPerEgg = computed(() => (totalEggs.value > 0 ? (Number(form.total_cost) || 0) / totalEggs.value : 0));

const submit = () => {
    form.post(route('purchases.store'));
};
</script>

<template>
    <Head title="Tambah Pembelian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <PageContainer>
            <PageHeader title="Tambah Pembelian" description="Catat telur masuk dari pemasok." />

            <form class="space-y-6" @submit.prevent="submit">
                <div class="grid gap-4 rounded-xl border border-border bg-card p-4 shadow-sm lg:grid-cols-3 md:p-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                        <div class="grid gap-2">
                            <Label for="purchase_date">Tanggal <span class="text-brand-danger">*</span></Label>
                            <Input id="purchase_date" v-model="form.purchase_date" type="date" required />
                            <InputError :message="form.errors.purchase_date" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="quantity">Jumlah ikat <span class="text-brand-danger">*</span></Label>
                            <Input id="quantity" v-model="form.quantity" type="number" min="1" inputmode="numeric" required />
                            <InputError :message="form.errors.quantity" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="egg_per_unit">Isi per ikat (butir) <span class="text-brand-danger">*</span></Label>
                            <Input id="egg_per_unit" v-model="form.egg_per_unit" type="number" min="1" inputmode="numeric" required />
                            <InputError :message="form.errors.egg_per_unit" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="total_cost">Total biaya (Rp) <span class="text-brand-danger">*</span></Label>
                            <Input id="total_cost" v-model="form.total_cost" type="number" min="0" step="500" inputmode="numeric" required />
                            <InputError :message="form.errors.total_cost" />
                        </div>

                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="notes">Catatan</Label>
                            <Textarea id="notes" v-model="form.notes" rows="3" placeholder="Catatan tambahan (opsional)" />
                            <InputError :message="form.errors.notes" />
                        </div>
                    </div>

                    <div class="space-y-3 rounded-lg bg-muted/50 p-4">
                        <p class="text-sm font-semibold text-foreground">Perhitungan otomatis</p>
                        <dl class="space-y-2 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-muted-foreground">Total telur</dt>
                                <dd class="font-medium text-foreground">{{ formatNumber(totalEggs) }} butir</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-muted-foreground">HPP / butir</dt>
                                <dd class="font-medium text-foreground">{{ costPerEgg > 0 ? formatRupiah(costPerEgg) : '-' }}</dd>
                            </div>
                        </dl>
                        <p class="text-xs text-muted-foreground">
                            HPP dihitung dari total biaya dibagi total telur dan disimpan sebagai layer stok.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Button type="submit" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Simpan Pembelian
                    </Button>
                    <Button variant="outline" type="button" as-child>
                        <Link :href="route('purchases.index')">Batal</Link>
                    </Button>
                </div>
            </form>
        </PageContainer>
    </AppLayout>
</template>
