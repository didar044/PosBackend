<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Brand;
class Categorie extends Model
{
      
    protected $table = 'categories';

    
    protected $fillable = [
        'name',
        'brand_id',
        'img',
        'description',
    ];

    
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
