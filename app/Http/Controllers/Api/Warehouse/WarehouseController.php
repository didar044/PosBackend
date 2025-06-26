<?php

namespace App\Http\Controllers\Api\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        return response()->json(Warehouse::all(), 200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
        ]);
        $warehouse = Warehouse::create($validated);
        return response()->json($warehouse, 201);
    }

    public function show($id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }
        return response()->json($warehouse, 200);
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'location' => 'nullable|string|max:255',
        ]);
        $warehouse->update($validated);
        return response()->json($warehouse, 200);
    }

    public function destroy($id)
    {
        $warehouse = Warehouse::find($id);
        if (!$warehouse) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }
        $warehouse->delete();
        return response()->json(['message' => 'Warehouse deleted'], 200);
    }
}
