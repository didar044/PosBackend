<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'unit_price', 'discount', 'tax_percent', 'tax_amount', 'subtotal'];
        public function product() {
        return $this->belongsTo(Product::class);
    }
}
