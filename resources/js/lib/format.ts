const idrFormatter = new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('id-ID');

/**
 * Format nilai rupiah tanpa desimal: 475000 -> "Rp475.000".
 */
export function formatRupiah(value: number | string | null | undefined): string {
    const numeric = toNumber(value);

    return `Rp${idrFormatter.format(Math.round(numeric))}`;
}

/**
 * Format angka dengan pemisah ribuan ala Indonesia: 1234 -> "1.234".
 */
export function formatNumber(value: number | string | null | undefined): string {
    return numberFormatter.format(toNumber(value));
}

/**
 * Format biaya per butir (dibulatkan ke rupiah penuh): "Rp2.639".
 */
export function formatCostPerEgg(value: number | string | null | undefined): string {
    return formatRupiah(value);
}

/**
 * Format tanggal panjang berbahasa Indonesia: "16 September 2026".
 */
export function formatDate(value: string | Date | null | undefined): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

/**
 * Format tanggal ringkas: "16 Sep 2026".
 */
export function formatShortDate(value: string | Date | null | undefined): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

/**
 * Format tanggal dan jam: "16 Sep 2026, 14:30".
 */
export function formatDateTime(value: string | Date | null | undefined): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

function toNumber(value: number | string | null | undefined): number {
    if (value === null || value === undefined || value === '') {
        return 0;
    }

    const numeric = typeof value === 'number' ? value : Number(value);

    return Number.isFinite(numeric) ? numeric : 0;
}
