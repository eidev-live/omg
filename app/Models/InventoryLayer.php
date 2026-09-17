<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'quantity_received',
        'quantity_remaining',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'quantity_received' => 'integer',
            'quantity_remaining' => 'integer',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function consumptions(): HasMany
    {
        return $this->hasMany(SaleItemConsumption::class);
    }

    /**
     * Layer yang masih memiliki sisa, diurutkan paling lama (FIFO).
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('quantity_remaining', '>', 0)
            ->orderBy('created_at')
            ->orderBy('id');
    }
}
