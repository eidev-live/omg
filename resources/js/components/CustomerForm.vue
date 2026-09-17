<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

interface CustomerData {
    id: number;
    name: string;
    phone: string | null;
    location: string | null;
    notes: string | null;
    is_active: boolean;
}

const props = defineProps<{
    customer?: CustomerData;
}>();

const form = useForm({
    name: props.customer?.name ?? '',
    phone: props.customer?.phone ?? '',
    location: props.customer?.location ?? '',
    notes: props.customer?.notes ?? '',
    is_active: props.customer?.is_active ?? true,
});

const submit = () => {
    if (props.customer) {
        form.put(route('customers.update', { customer: props.customer.id }));
    } else {
        form.post(route('customers.store'));
    }
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-4 rounded-xl border border-border bg-card p-4 shadow-sm sm:grid-cols-2 md:p-6">
            <div class="grid gap-2">
                <Label for="name">Nama <span class="text-brand-danger">*</span></Label>
                <Input id="name" v-model="form.name" required autofocus autocomplete="name" placeholder="Nama customer" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="phone">Telepon</Label>
                <Input id="phone" v-model="form.phone" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx" />
                <InputError :message="form.errors.phone" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="location">Lokasi</Label>
                <Input id="location" v-model="form.location" placeholder="Alamat / daerah" />
                <InputError :message="form.errors.location" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="notes">Catatan</Label>
                <Textarea id="notes" v-model="form.notes" rows="3" placeholder="Catatan tambahan (opsional)" />
                <InputError :message="form.errors.notes" />
            </div>

            <div class="sm:col-span-2">
                <Label for="is_active" class="flex items-center gap-3">
                    <Checkbox id="is_active" v-model:checked="form.is_active" />
                    <span>Customer aktif</span>
                </Label>
                <p class="mt-1 text-xs text-muted-foreground">Customer nonaktif tidak muncul saat membuat transaksi baru.</p>
                <InputError :message="form.errors.is_active" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                {{ customer ? 'Simpan Perubahan' : 'Simpan' }}
            </Button>
            <Button variant="outline" type="button" as-child>
                <Link :href="route('customers.index')">Batal</Link>
            </Button>
        </div>
    </form>
</template>
