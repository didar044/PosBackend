<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
        protected $table = 'warehouses';

    
    protected $fillable = [
        'name',
        'location',
    ];
}
