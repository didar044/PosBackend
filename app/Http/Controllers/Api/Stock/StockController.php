<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stock\Stock;

class StockController extends Controller
{
    
    public function index()
        {
            $stocks = Stock::with('warehouse', 'product')->get();
            return response()->json($stocks);
        }


    
    public function store(Request $request)
    {
        // $stock= new Stock();
        // $stock->product_id=request()->product_id;
        // $stock->warehouse_id=request()->warehouse_id;
        // $stock->quantity=request()->quantity;
        // $stock->save();
        // return response()->json($stock);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
