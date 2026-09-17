<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'sale_type_id',
        'sale_type_name',
        'quantity',
        'egg_quantity',
        'unit_price',
        'subtotal',
        'unit_cost',
        'total_cost',
        'profit',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'egg_quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'profit' => 'decimal:4',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleType(): BelongsTo
    {
        return $this->belongsTo(SaleType::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(SaleItemConsumption::class);
    }
}
