<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers=Supplier::all();
        return response()->json($suppliers);
    }

    
    public function store(Request $request)
    {
        $suppliers=new Supplier();
        $suppliers->name=$request->name;
        $suppliers->phone=$request->phone;
        $suppliers->email=$request->email;
        $suppliers->address=$request->address;
        $suppliers->save();
        return response()->json(['suppliers'=>$suppliers]);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier=Supplier::findOrFail($id);
        $supplier->name=$request->name;
        $supplier->phone=$request->phone;
        $supplier->email=$request->email;
        $supplier->address=$request->address;
        $supplier->save();
        return response()->json(['supplier'=>$supplier]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $supplier=Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['message' => 'Category deleted successfully']);

    }
}
