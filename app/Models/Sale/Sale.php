<?php

namespace App\Models\Sale;

use Illuminate\Database\Eloquent\Model;
use App\Models\Warehouse\Warehouse;
use App\Models\Product\Product;
use App\Models\Customer\Customer;
use App\Models\Sale\SaleItem;

class Sale extends Model
{       


     protected $table = 'sales';
    protected $fillable = [
        'customer_id', 'warehouse_id', 'sale_date',
        'total_amount', 'paid_amount', 'payment_method', 'status',
    ];
     public function warehouse() {
        return $this->belongsTo(Warehouse::class,'warehouse_id');
    }
     public function customer() {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    
    public function product() {
        return $this->belongsTo(Product::class);
    }
    public function items() {
        return $this->hasMany(SaleItem::class,'sale_id');
    }
}
