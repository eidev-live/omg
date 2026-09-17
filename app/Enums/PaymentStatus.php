<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'UNPAID';
    case Partial = 'PARTIAL';
    case Paid = 'PAID';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Lunas',
            self::Partial => 'Sebagian',
            self::Paid => 'Lunas',
        };
    }
}
