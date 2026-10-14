<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'unit_price',
        'cost_price',
    ];

    public $timestamps = false;

    public function inventoryLevel(): HasOne
    {
        return $this->hasOne(InventoryLevel::class);
    }
}