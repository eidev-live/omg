<script setup lang="ts">
import AppLogoImage from '@/components/AppLogoImage.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status: number;
}>();

const titles: Record<number, string> = {
    403: 'Akses ditolak',
    404: 'Halaman tidak ditemukan',
    419: 'Sesi sudah berakhir',
    500: 'Terjadi kesalahan',
    503: 'Sedang dalam perbaikan',
};

const descriptions: Record<number, string> = {
    403: 'Anda tidak memiliki izin untuk membuka halaman ini.',
    404: 'Halaman yang Anda cari tidak ada atau sudah dipindahkan.',
    419: 'Silakan muat ulang halaman dan coba lagi.',
    500: 'Terjadi masalah pada server. Silakan coba beberapa saat lagi.',
    503: 'Aplikasi sedang dalam perbaikan. Silakan kembali nanti.',
};

const title = computed(() => titles[props.status] ?? 'Terjadi kesalahan');
const description = computed(() => descriptions[props.status] ?? 'Silakan coba lagi.');
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 text-center">
        <AppLogoImage class="h-20 w-auto" />

        <div class="space-y-2">
            <p class="text-sm font-semibold text-muted-foreground">Error {{ status }}</p>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ title }}</h1>
            <p class="max-w-md text-sm text-muted-foreground">{{ description }}</p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-3">
            <Button as-child>
                <Link href="/dashboard">Kembali ke Dashboard</Link>
            </Button>
            <Button variant="outline" as-child>
                <Link href="/">Ke Beranda</Link>
            </Button>
        </div>
    </div>
</template>
