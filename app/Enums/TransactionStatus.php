<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Active = 'ACTIVE';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
