<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'PENDING';
    case Shipped = 'SHIPPED';
    case Delivered = 'DELIVERED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Shipped => 'Dikirim',
            self::Delivered => 'Terkirim',
        };
    }
}
