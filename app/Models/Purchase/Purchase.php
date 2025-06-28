<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase\PurchaseItem;
use App\Models\Supplier\Supplier;
use App\Models\Warehouse\Warehouse;
use App\Models\Product\Product;

class Purchase extends Model
{
    protected $fillable = ['supplier_id', 'warehouse_id', 'purchase_date', 'total_amount', 'paid_amount', 'status', 'reference', 'description'];


    public function items() {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    // PurchaseItem.php
    public function product() {
        return $this->belongsTo(Product::class);
    }

}
