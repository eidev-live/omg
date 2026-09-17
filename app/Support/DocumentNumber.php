<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DocumentNumber
{
    /**
     * Menghasilkan nomor dokumen berurutan per hari, contoh: PUR-20260916-0001.
     */
    public static function next(string $table, string $column, string $prefix, ?string $date = null): string
    {
        $day = $date ?? now()->format('Ymd');
        $pattern = "{$prefix}-{$day}-%";

        $last = DB::table($table)
            ->where($column, 'like', $pattern)
            ->orderByDesc($column)
            ->value($column);

        $sequence = $last !== null ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $day, $sequence);
    }
}
