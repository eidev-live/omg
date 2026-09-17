<script setup lang="ts">
import AppLogoImage from '@/components/AppLogoImage.vue';
import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { BarChart3, Boxes, PackagePlus, ShoppingCart } from 'lucide-vue-next';

const page = usePage<SharedData>();
const isAuthenticated = Boolean(page.props.auth?.user);

const highlights = [
    { title: 'Catat pembelian', description: 'Stok dan HPP per butir dihitung otomatis.', icon: PackagePlus },
    { title: 'Stok FIFO', description: 'Batch terlama keluar lebih dulu.', icon: Boxes },
    { title: 'Transaksi penjualan', description: 'Multi-item dengan validasi stok.', icon: ShoppingCart },
    { title: 'Laba & laporan', description: 'Revenue, COGS, dan margin yang jelas.', icon: BarChart3 },
];
</script>

<template>
    <Head title="Selamat Datang" />

    <div class="flex min-h-svh flex-col bg-background">
        <header class="flex items-center justify-between px-6 py-5 md:px-10">
            <AppLogoImage class="h-12 w-auto" />
            <Button v-if="isAuthenticated" as-child>
                <Link :href="route('dashboard')">Buka Dashboard</Link>
            </Button>
            <Button v-else as-child>
                <Link :href="route('login')">Masuk</Link>
            </Button>
        </header>

        <main class="flex flex-1 items-center justify-center px-6 py-8 md:px-10">
            <div class="mx-auto w-full max-w-2xl text-center">
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

                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                    <Button v-if="isAuthenticated" size="lg" as-child>
                        <Link :href="route('dashboard')">Buka Dashboard</Link>
                    </Button>
                    <Button v-else size="lg" as-child>
                        <Link :href="route('login')">Masuk</Link>
                    </Button>
                </div>

                <dl class="mt-12 grid gap-4 text-left sm:grid-cols-2">
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
            </div>
        </main>

        <footer class="px-6 py-6 text-center text-xs text-muted-foreground md:px-10">Oh My Egg &middot; Sistem penjualan & stok telur</footer>
    </div>
</template>
