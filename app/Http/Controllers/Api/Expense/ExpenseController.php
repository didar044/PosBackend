<?php

namespace App\Http\Controllers\Api\Expense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense\Expense; 
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Return all expenses with their category info
        $expenses = Expense::with('category')->get();
        return response()->json($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'reference_number' => ['required', 'string', 'max:100', 'unique:expenses,reference_number'],
            'expense_for' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $expense = Expense::with('category')->findOrFail($id);
        return response()->json($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'reference_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('expenses')->ignore($expense->id),
            ],
            'expense_for' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
