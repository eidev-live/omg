<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly int $available,
        public readonly int $required,
    ) {
        parent::__construct("Stok telur tidak mencukupi. Tersedia {$available} butir, dibutuhkan {$required} butir.");
    }
}
