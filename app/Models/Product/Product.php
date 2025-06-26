<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Supplier\Supplier;
use App\Models\Product\Brand;
use App\Models\Product\Categorie;

class Product extends Model
{
    protected $table = 'products';  

   protected $fillable = [
            'name',
            'brand_id',
            'categorie_id',
            'supplier_id',
            'barcode',
            'price',
            'discount',
            'tax',
            'quantity',
            'status',
            'img',
            'description',
        ];


    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
      public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
      public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
}

