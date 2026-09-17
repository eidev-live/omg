<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <HeadingSmall title="Hapus akun" description="Hapus akun beserta seluruh data terkait" />

        <div class="space-y-4 rounded-lg border border-brand-danger/20 bg-brand-danger/10 p-4">
            <div class="relative space-y-0.5 text-brand-danger">
                <p class="font-medium">Peringatan</p>
                <p class="text-sm">Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive">Hapus Akun</Button>
                </DialogTrigger>
                <DialogContent class="w-[calc(100%-2rem)] max-w-md sm:w-full">
                    <form class="space-y-6" @submit.prevent="deleteUser">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Yakin ingin menghapus akun Anda?</DialogTitle>
                            <DialogDescription>
                                Setelah akun dihapus, seluruh data akan hilang permanen. Masukkan password Anda untuk mengonfirmasi.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="delete-password" class="sr-only">Password</Label>
                            <Input
                                id="delete-password"
                                ref="passwordInput"
                                v-model="form.password"
                                type="password"
                                name="password"
                                placeholder="Password"
                                autocomplete="current-password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter>
                            <DialogClose as-child>
                                <Button variant="secondary" type="button" @click="closeModal">Batal</Button>
                            </DialogClose>

                            <Button variant="destructive" type="submit" :disabled="form.processing">Hapus Akun</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
