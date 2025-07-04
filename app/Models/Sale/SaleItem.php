<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
class SaleItem extends Model
{
       

    protected $table = 'sale_items';
    protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];
         public function product() {
        return $this->belongsTo(Product::class);
    }


}
