<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Load supplier, brand, and category relationships, paginate 10 per page
        $products = Product::with(['supplier', 'brand', 'categorie'])->paginate(10);
        return response()->json($products, 200);
    }

public function store(Request $request)
{

    try {
        \Log::info('Incoming request data:', $request->all());
    $product = new Product();
         
    $product->name = $request->input('name');
    $product->brand_id = $request->input('brand_id');
    $product->categorie_id = $request->input('categorie_id');
    $product->supplier_id = $request->input('supplier_id');
    $product->barcode = $request->input('barcode');
    $product->price = $request->input('price');
    $product->discount = $request->input('discount', 0);
    $product->tax = $request->input('tax', 0);
    $product->quantity = $request->input('quantity');
    $product->status = $request->input('status', 'active');
    $product->description = $request->input('description');

    $product->save();

    if ($request->hasFile('img')) {
        $image = $request->file('img');
        $imageName = 'product_' . $product->id . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('img/product'), $imageName);
        $product->img = $imageName;
        $product->save();
    }

    return response()->json($product, 201);
     } catch (\Exception $e) {
        \Log::error('Product store error: '.$e->getMessage());
        return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
    }
}

    public function show($id)
    {
        $product = Product::with(['supplier', 'brand', 'categorie'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

public function update(Request $request, $id)
{
    // Log incoming request data for debugging
    \Log::info('Update request data:', $request->all());

    $product = Product::find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    // Validate request data
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'brand_id' => 'required|integer|exists:brands,id',
        'categorie_id' => 'required|integer|exists:categories,id',
        'supplier_id' => 'required|integer|exists:suppliers,id',
        'barcode' => 'nullable|string|max:255',
        'price' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'status' => 'nullable|in:active,inactive',
        'description' => 'nullable|string',
        'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    // Handle image upload if exists
    if ($request->hasFile('img')) {
        $image = $request->file('img');
        $imageName = 'product_' . $product->id . '.' . $image->getClientOriginalExtension();
        $destination = public_path('img/product');

        // Delete old image if exists
        if (!empty($product->img) && file_exists($destination . '/' . $product->img)) {
            @unlink($destination . '/' . $product->img);
        }

        $image->move($destination, $imageName);
        $validated['img'] = $imageName;
    }

    // Fill product with validated data and save
    $product->fill($validated);
    $product->save();

    return response()->json([
        'message' => 'Product updated successfully',
        'product' => $product
    ], 200);
}


    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if ($product->img && file_exists(public_path('img/product/' . $product->img))) {
            @unlink(public_path('img/product/' . $product->img));
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted'], 200);
    }
}
