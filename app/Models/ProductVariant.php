<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'variant_attributes', 'cost_price', 'selling_price', 'image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'variant_attributes' => 'array',
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function getLabelAttribute(): string
    {
        $attrs = collect($this->variant_attributes ?? [])
            ->map(fn ($value, $key) => "{$key}: {$value}")
            ->implode(', ');

        return $attrs !== '' ? "{$this->sku} ({$attrs})" : $this->sku;
    }
}
