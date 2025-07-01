<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Warehouse\Warehouse;

class Stocktransfer extends Model
{   

    protected $fillable = [
        'product_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'quantity',
        'transfer_date',
        'status',
        'description'
    ];
     protected $table = 'stock_transfers';
   
    public function fromWarehouse() {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    
    public function toWarehouse() {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
