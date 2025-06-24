<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Categorie;
use Illuminate\Support\Facades\Validator;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::with('brand')->get();;
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $category = new Categorie();
        $category->name = $request->name;
        $category->brand_id = $request->brand_id;
        $category->description = $request->description;

        if ($request->hasFile('img')) {
            $image = $request->file('img');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('img/categorie'), $imageName);
            $category->img = $imageName;
        }

        $category->save();

        return response()->json(['category' => $category], 201);
    }

    public function show($id)
    {
        $category = Categorie::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function update(Request $request, string $id)
    {
        $category = Categorie::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->name = $request->name;
        $category->brand_id = $request->brand_id;
        $category->description = $request->description;

        if ($request->hasFile('img')) {
            // Delete old image if exists
            if ($category->img && file_exists(public_path('img/categorie/' . $category->img))) {
                unlink(public_path('img/categorie/' . $category->img));
            }

            // Upload new image
            $image = $request->file('img');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('img/categorie'), $imageName);
            $category->img = $imageName;
        }

        $category->save();

        return response()->json(['category' => $category], 200);
    }


    public function destroy($id)
    {
        $category = Categorie::find($id);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }
}
