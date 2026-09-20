<script setup lang="ts">
import AppLogoImage from '@/components/AppLogoImage.vue';
import InputError from '@/components/InputError.vue';
import PageContainer from '@/components/PageContainer.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { BarChart3, Boxes, LoaderCircle, PackagePlus, ShoppingCart } from 'lucide-vue-next';

const page = usePage<SharedData>();
const isAuthenticated = Boolean(page.props.auth?.user);

const highlights = [
    { title: 'Catat pembelian', description: 'Stok dan HPP per butir dihitung otomatis.', icon: PackagePlus },
    { title: 'Stok FIFO', description: 'Batch terlama keluar lebih dulu.', icon: Boxes },
    { title: 'Transaksi penjualan', description: 'Multi-item dengan validasi stok.', icon: ShoppingCart },
    { title: 'Laba & laporan', description: 'Revenue, COGS, dan margin yang jelas.', icon: BarChart3 },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    website: '',
});

const submit = () => {
    form.post(route('leads.store'));
};
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="flex min-h-svh flex-col bg-background">
        <header class="flex items-center justify-between px-6 py-5 md:px-10">
            <AppLogoImage class="h-12 w-auto" />
            <Button v-if="isAuthenticated" as-child>
                <Link :href="route('dashboard')">Buka Dashboard</Link>
            </Button>
        </header>

        <main class="flex-1">
            <PageContainer>
                <section class="py-8 text-center md:py-12">
                    <p class="inline-flex items-center rounded-full bg-accent px-3 py-1 text-xs font-medium text-accent-foreground">
                        Kelola bisnis telur tanpa ribet
                    </p>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-foreground md:text-4xl">
                        Pembelian, stok, penjualan, dan laba dalam satu tempat.
                    </h1>
                    <p class="mx-auto mt-4 max-w-xl text-base text-muted-foreground">
                        Oh My Egg membantu pemilik usaha kecil mencatat setiap butir telur masuk dan keluar, menghitung HPP dengan metode FIFO, serta
                        memantau pembayaran dan pengiriman.
                    </p>
                </section>

                <section class="py-6">
                    <dl class="grid gap-4 text-left sm:grid-cols-2">
                        <div v-for="item in highlights" :key="item.title" class="rounded-xl border border-border bg-card p-4 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-accent-foreground">
                                    <component :is="item.icon" class="size-4" />
                                </span>
                                <dt class="text-sm font-semibold text-foreground">{{ item.title }}</dt>
                            </div>
                            <dd class="mt-2 text-sm text-muted-foreground">{{ item.description }}</dd>
                        </div>
                    </dl>
                </section>

                <section id="pesan" class="scroll-mt-24 py-8">
                    <div class="mx-auto max-w-xl rounded-2xl border border-border bg-card p-5 shadow-sm md:p-8">
                        <h2 class="text-xl font-semibold tracking-tight text-foreground md:text-2xl">Tertarik atau ingin bertanya?</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Isi data berikut. Setelah dikirim, Anda akan diarahkan ke WhatsApp kami.</p>

                        <form class="mt-6 space-y-4" @submit.prevent="submit">
                            <div class="grid gap-2">
                                <Label for="name">Nama <span class="text-brand-danger">*</span></Label>
                                <Input id="name" v-model="form.name" required autocomplete="name" placeholder="Nama Anda" />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="email">Email <span class="text-brand-danger">*</span></Label>
                                <Input id="email" v-model="form.email" type="email" required autocomplete="email" placeholder="email@example.com" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="phone">No. WhatsApp <span class="text-brand-danger">*</span></Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    inputmode="tel"
                                    required
                                    autocomplete="tel"
                                    placeholder="08xxxxxxxxxx"
                                />
                                <InputError :message="form.errors.phone" />
                            </div>

                            <div class="hidden" aria-hidden="true">
                                <label for="website">Website</label>
                                <input id="website" v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
                            </div>

                            <p class="text-xs text-muted-foreground">Dengan mengirim, Anda setuju dihubungi oleh Oh My Egg melalui WhatsApp.</p>

                            <Button type="submit" class="w-full" size="lg" :disabled="form.processing">
                                <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                Kirim &amp; Chat WhatsApp
                            </Button>
                            <InputError :message="form.errors.website" />
                        </form>
                    </div>
                </section>
            </PageContainer>
        </main>

        <footer class="px-6 py-6 text-center text-xs text-muted-foreground md:px-10">Oh My Egg &middot; Sistem penjualan &amp; stok telur</footer>
    </div>
</template>
