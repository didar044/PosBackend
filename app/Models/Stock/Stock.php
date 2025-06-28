<?php

namespace App\Models\Stock;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'quantity'];
}
