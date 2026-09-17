<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface SaleTypeData {
    id: number;
    name: string;
    egg_quantity: number;
    selling_price: number;
    is_active: boolean;
}

const props = defineProps<{
    saleType?: SaleTypeData;
}>();

const form = useForm({
    name: props.saleType?.name ?? '',
    egg_quantity: props.saleType?.egg_quantity ?? 10,
    selling_price: props.saleType?.selling_price ?? 0,
    is_active: props.saleType?.is_active ?? true,
});

const submit = () => {
    if (props.saleType) {
        form.put(route('sale-types.update', { sale_type: props.saleType.id }));
    } else {
        form.post(route('sale-types.store'));
    }
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-4 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-2 md:p-6">
            <div class="grid gap-2 sm:col-span-2">
                <Label for="name">Nama tipe <span class="text-brand-danger">*</span></Label>
                <Input id="name" v-model="form.name" required autofocus placeholder="Contoh: Pack, Tray, Ikat" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="egg_quantity">Isi (butir) <span class="text-brand-danger">*</span></Label>
                <Input id="egg_quantity" v-model="form.egg_quantity" type="number" min="1" inputmode="numeric" required />
                <InputError :message="form.errors.egg_quantity" />
            </div>

            <div class="grid gap-2">
                <Label for="selling_price">Harga jual (Rp) <span class="text-brand-danger">*</span></Label>
                <Input id="selling_price" v-model="form.selling_price" type="number" min="0" step="500" inputmode="numeric" required />
                <InputError :message="form.errors.selling_price" />
            </div>

            <div class="sm:col-span-2">
                <Label for="is_active" class="flex items-center gap-3">
                    <Checkbox id="is_active" v-model:checked="form.is_active" />
                    <span>Aktif</span>
                </Label>
                <p class="mt-1 text-xs text-muted-foreground">
                    Tipe nonaktif tidak muncul saat membuat transaksi baru, tetapi tetap tampil pada histori.
                </p>
                <InputError :message="form.errors.is_active" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                {{ saleType ? 'Simpan Perubahan' : 'Simpan' }}
            </Button>
            <Button variant="outline" type="button" as-child>
                <Link :href="route('sale-types.index')">Batal</Link>
            </Button>
        </div>
    </form>
</template>
