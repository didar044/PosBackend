<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Warehouse\Warehouse;

class Stock extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];
    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    // PurchaseItem.php
    public function product() {
        return $this->belongsTo(Product::class);
    }

}
