<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Purchase = 'PURCHASE';
    case Sale = 'SALE';
    case AdjustmentIn = 'ADJUSTMENT_IN';
    case AdjustmentOut = 'ADJUSTMENT_OUT';
    case SaleCancel = 'SALE_CANCEL';
    case PurchaseCancel = 'PURCHASE_CANCEL';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Pembelian',
            self::Sale => 'Penjualan',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::SaleCancel => 'Pembatalan Penjualan',
            self::PurchaseCancel => 'Pembatalan Pembelian',
        };
    }
}
