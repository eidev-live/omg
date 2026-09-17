<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'egg_quantity',
        'selling_price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'egg_quantity' => 'integer',
            'selling_price' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
