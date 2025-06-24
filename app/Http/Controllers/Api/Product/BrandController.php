<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Brand;

class BrandController extends Controller
{

    public function index()
    {
        $brands = Brand::all();
        return response()->json(['brands' => $brands]);
    }


    public function store(Request $request)
    {

        $brands = new Brand();
        $brands->name = $request->name;
        $brands->description = $request->description;

        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('img/brand'), $imageName);
            $brands->img = $imageName;
        }

        $brands->save();

        return response()->json(['brands' => $brands], 201);
    }


    public function show(string $id)
    {
            $brand = Brand::find($id);

            if (!$brand) {
                return response()->json(['message' => 'Brand not found'], 404);
            }

            return response()->json(['brand' => $brand], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }

        $brand->name = $request->name;
        $brand->description = $request->description;

        if ($request->hasFile('img')) {
            if ($brand->img && file_exists(public_path('img/brand/' . $brand->img))) {
                unlink(public_path('img/brand/' . $brand->img));
            }

            $image = $request->file('img');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('img/brand'), $imageName);
            $brand->img = $imageName;
        }

        $brand->save();

        return response()->json(['brand' => $brand], 200);
    }




    public function destroy(string $id)
    {
        $brand = Brand::find($id);
        $brand->delete();
    }
}
