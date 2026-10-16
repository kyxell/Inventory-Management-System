<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    protected $fillable = ['product_id', 'quantity_change', 'new_quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}