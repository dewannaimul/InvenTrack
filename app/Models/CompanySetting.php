<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    protected $fillable = [
        'name', 'address', 'phone', 'email', 'tax_id', 'logo',
        'currency_code', 'currency_symbol', 'invoice_footer', 'receipt_footer', 'default_warehouse_id',
    ];

    public function defaultWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
