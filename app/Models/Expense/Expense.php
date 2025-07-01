<?php

namespace App\Models\Expense;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'expense_category_id',
        'reference_number',
        'expense_for',
        'amount',
        'expense_date',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategorie::class, 'expense_category_id');
    }
}


