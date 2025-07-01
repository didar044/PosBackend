<?php

namespace App\Http\Controllers\Api\Expense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense\ExpenseCategorie;

class ExpenseCategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(ExpenseCategorie::all());
    }

    
    public function store(Request $request)
    {
        $expensecategorie=new ExpenseCategorie();
        $expensecategorie->name=request()->name;
        $expensecategorie->description=request()->description;
        $expensecategorie->save();
        return response()->json($expensecategorie);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       

        $category = ExpenseCategorie::findOrFail($id);
        return response()->json($category);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = ExpenseCategorie::findOrFail($id);
        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
            {
                $category = ExpenseCategorie::findOrFail($id);
                $category->delete();

                return response()->json(['message' => 'Deleted successfully']);
            }
}
