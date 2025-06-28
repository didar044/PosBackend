<?php

namespace App\Http\Controllers\Api\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase\Purchase;
use App\Models\Purchase\PurchaseItem;
use App\Models\Stock\Stock;

class PurchaseController extends Controller
{
    // List all purchases with pagination
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'warehouse', 'items.product'])
            ->orderBy('purchase_date', 'desc')
            ->paginate(15);

        return response()->json($purchases);
    }

    // Store a new purchase with items & update stock

public function store(Request $request)
{
    \Log::info('Purchase request data:', $request->all());

    // $request->validate([
    //     'supplier_id' => 'required|integer|exists:suppliers,id',
    //     'warehouse_id' => 'required|integer|exists:warehouses,id',
    //     'purchase_date' => 'required|date',
    //     'total_amount' => 'required|numeric|min:0',
    //     'paid_amount' => 'nullable|numeric|min:0',
    //     'status' => 'required|in:pending,completed,cancelled',
    //     'reference' => 'nullable|string|max:100',
    //     'description' => 'nullable|string',
    //     'items' => 'required|array|min:1',
    //     'items.*.product_id' => 'required|integer|exists:products,id',
    //     'items.*.quantity' => 'required|numeric|min:0',
    //     'items.*.unit_price' => 'required|numeric|min:0',
    //     'items.*.discount' => 'nullable|numeric|min:0',
    //     'items.*.tax_percent' => 'nullable|numeric|min:0',
    //     'items.*.tax_amount' => 'nullable|numeric|min:0',
    //     'items.*.subtotal' => 'required|numeric|min:0',
    // ]);

    DB::beginTransaction();

    try {
        $supplier_id = $request->input('supplier_id');
        $warehouse_id = $request->input('warehouse_id');
        $purchase_date = $request->input('purchase_date');
        $total_amount = $request->input('total_amount');
        $paid_amount = $request->input('paid_amount', 0);
        $status = $request->input('status', 'pending');
        $reference = $request->input('reference');
        $description = $request->input('description');
        $items = $request->input('items');

        // Create the purchase record
        $purchase = Purchase::create([
            'supplier_id' => $supplier_id,
            'warehouse_id' => $warehouse_id,
            'purchase_date' => $purchase_date,
            'total_amount' => $total_amount,
            'paid_amount' => $paid_amount,
            'status' => $status,
            'reference' => $reference,
            'description' => $description,
        ]);

        foreach ($items as $item) {
            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount' => $item['discount'] ?? 0,
                'tax_percent' => $item['tax_percent'] ?? 0,
                'tax_amount' => $item['tax_amount'] ?? 0,
                'subtotal' => $item['subtotal'],
            ]);

            // Update stock only if purchase status is 'completed'
            if ($status === 'completed') {
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $warehouse_id)
                    ->first();

                if ($stock) {
                    $stock->quantity += $item['quantity'];
                    $stock->save();
                } else {
                    Stock::create([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $warehouse_id,
                        'quantity' => $item['quantity'],
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'message' => 'Purchase created successfully',
            'purchase' => $purchase->load('items'),
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        \Log::error('Purchase creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request' => $request->all(),
        ]);

        return response()->json([
            'message' => 'Failed to create purchase',
            'error' => $e->getMessage(),
        ], 500);
    }
}










public function updateStatus(Request $request, $id)
{
    \Log::info("Update status request for purchase ID: {$id} with status: " . $request->input('status'));

    $request->validate([
        'status' => 'required|in:pending,completed,cancelled',
    ]);

    try {
        $purchase = Purchase::with('items')->findOrFail($id);
    } catch (\Exception $e) {
        \Log::error('Purchase fetch failed: '.$e->getMessage());
        return response()->json(['message' => 'Purchase not found'], 404);
    }

    $oldStatus = $purchase->status;
    $newStatus = $request->input('status');

    if ($oldStatus === $newStatus) {
        return response()->json(['message' => 'Status is already ' . $newStatus], 200);
    }

    DB::beginTransaction();

    try {
        $updateStock = function ($increase = true) use ($purchase) {
            foreach ($purchase->items as $item) {
                $qtyChange = $increase ? $item->quantity : -$item->quantity;

                \Log::info("Updating stock for product_id {$item->product_id}, warehouse_id {$purchase->warehouse_id}, qtyChange: {$qtyChange}");

                $stock = Stock::firstOrNew([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                ]);

                $stock->quantity = max(0, ($stock->quantity ?? 0) + $qtyChange);
                $stock->save();
            }
        };

        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            $updateStock(false);
        } elseif ($oldStatus !== 'completed' && $newStatus === 'completed') {
            $updateStock(true);
        }

        $purchase->status = $newStatus;
        $purchase->save();

        DB::commit();

        return response()->json(['message' => 'Status updated successfully']);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update status: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to update status',
            'error' => $e->getMessage(),
        ], 500);
    }
}











            // Show a specific purchase with items and related data
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'warehouse', 'items.product'])->find($id);

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        return response()->json($purchase);
    }

    // Update purchase & items, adjust stock accordingly
    public function update(Request $request, $id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|integer|exists:pos_suppliers,id',
            'warehouse_id' => 'required|integer|exists:pos_warehouses,id',
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,completed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:pos_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // 1. Reverse old stock quantities (reduce stock for old purchase items)
            foreach ($purchase->items as $oldItem) {
                $stock = Stock::where('product_id', $oldItem->product_id)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->first();

                if ($stock) {
                    $stock->quantity -= $oldItem->quantity;
                    if ($stock->quantity < 0) $stock->quantity = 0; // no negative stock
                    $stock->save();
                }
            }

            // 2. Update purchase main data
            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $validated['total_amount'],
                'paid_amount' => $validated['paid_amount'] ?? 0,
                'status' => $validated['status'] ?? 'pending',
            ]);

            // 3. Delete old purchase items
            $purchase->items()->delete();

            // 4. Insert new purchase items & update stock
            foreach ($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);

                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->first();

                if ($stock) {
                    $stock->quantity += $item['quantity'];
                    $stock->save();
                } else {
                    Stock::create([
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $purchase->warehouse_id,
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Purchase updated successfully',
                'purchase' => $purchase->load('items'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update purchase',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete purchase & reduce stock accordingly
    public function destroy($id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }

        DB::beginTransaction();

        try {
            // Reduce stock quantity for each purchase item
            foreach ($purchase->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $purchase->warehouse_id)
                    ->first();

                if ($stock) {
                    $stock->quantity -= $item->quantity;
                    if ($stock->quantity < 0) $stock->quantity = 0; // no negative stock
                    $stock->save();
                }
            }

            // Delete purchase items and purchase
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return response()->json(['message' => 'Purchase deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete purchase',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}


