<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'sku', 'barcode', 'name', 'slug', 'description', 'category_id', 'brand_id',
        'unit_id', 'cost_price', 'selling_price', 'tax_rate', 'reorder_level',
        'image', 'has_variants', 'track_stock', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'reorder_level' => 'integer',
            'has_variants' => 'boolean',
            'track_stock' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['sku', 'name', 'cost_price', 'selling_price', 'reorder_level', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->track_stock && $this->total_stock <= $this->reorder_level;
    }
}
