<?php

namespace App\Http\Controllers\Api\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale\SaleItem;
use Illuminate\Support\Facades\DB;
use App\Models\Sale\Sale;
use App\Models\Stock\Stock;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('items','warehouse','customer','product')->get();
        return response()->json($sales);
    }

    public function store(Request $request)
        {
            $request->validate([
                'customer_id'        => 'nullable|integer',
                'warehouse_id'       => 'required|integer',
                'sale_date'          => 'required|date',
                'total_amount'       => 'required|numeric',
                'paid_amount'        => 'required|numeric',
                'payment_method'     => 'required|in:cash,card,mobile,bank',
                'status'             => 'required|in:pending,completed,cancelled',
                'items'              => 'required|array|min:1',
                'items.*.product_id' => 'required|integer',
                'items.*.quantity'   => 'required|integer',
                'items.*.unit_price' => 'required|numeric',
                'items.*.subtotal'   => 'required|numeric',
            ]);

            DB::beginTransaction();

            try {
                $sale = Sale::create([
                    'customer_id'    => $request->customer_id,
                    'warehouse_id'   => $request->warehouse_id,
                    'sale_date'      => $request->sale_date,
                    'total_amount'   => $request->total_amount,
                    'paid_amount'    => $request->paid_amount,
                    'payment_method' => $request->payment_method,
                    'status'         => $request->status,
                ]);

                foreach ($request->items as $item) {
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal'   => $item['subtotal'],
                    ]);

                    // Adjust stock only if sale is completed
                    if ($request->status === 'completed') {
                        $stock = Stock::where('product_id', $item['product_id'])
                                    ->where('warehouse_id', $request->warehouse_id)
                                    ->lockForUpdate()
                                    ->first();

                        if (!$stock) {
                            throw new \Exception("Stock not found for product ID {$item['product_id']} in warehouse ID {$request->warehouse_id}");
                        }

                        if ($stock->quantity < $item['quantity']) {
                            throw new \Exception("Insufficient stock for product ID {$item['product_id']}");
                        }

                        $stock->quantity -= $item['quantity'];
                        $stock->save();
                    }
                }

                DB::commit();
                return response()->json(['message' => 'Sale created successfully', 'sale_id' => $sale->id], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Failed to create sale', 'details' => $e->getMessage()], 500);
            }
        }


// SaleController.php

public function updateStatus(Request $request, $id)
{
    \Log::info("UpdateStatus called for Sale with ID: $id and status: " . $request->input('status'));

    $request->validate([
        'status' => 'required|in:pending,completed,cancelled',
    ]);

    try {
        $sale = Sale::with(['items'])->findOrFail($id);
    } catch (\Exception $e) {
        \Log::error("Sale not found: " . $e->getMessage());
        return response()->json(['message' => 'Sale not found'], 404);
    }

    $oldStatus = $sale->status;
    $newStatus = $request->input('status');

    if ($oldStatus === $newStatus) {
        return response()->json(['message' => 'Status is already ' . $newStatus], 200);
    }

    DB::beginTransaction();

    try {
        // Stock adjustment logic
        $adjustStock = function ($direction) use ($sale) {
            foreach ($sale->items as $item) {
                $stock = Stock::firstOrNew([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $sale->warehouse_id,
                ]);

                $newQty = $direction === 'add'
                    ? $stock->quantity + $item->quantity
                    : $stock->quantity - $item->quantity;

                if ($newQty < 0) {
                    throw new \Exception("Insufficient stock for product_id {$item->product_id}");
                }

                $stock->quantity = $newQty;
                $stock->save();
            }
        };

        // Determine when to subtract or add back stock
        if ($oldStatus !== 'completed' && $newStatus === 'completed') {
            // Sale marked completed → subtract stock
            $adjustStock('subtract');
        } elseif ($oldStatus === 'completed' && in_array($newStatus, ['pending', 'cancelled'])) {
            // Sale canceled or reverted → add stock back
            $adjustStock('add');
        }

        // Save status
        $sale->status = $newStatus;
        $sale->save();

        DB::commit();
        return response()->json(['message' => 'Sale status updated successfully']);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Failed to update sale status: " . $e->getMessage());
        return response()->json(['message' => 'Failed to update sale status', 'error' => $e->getMessage()], 500);
    }
}


 public function show(string $id)
    {
        $sale = Sale::with('items','warehouse','customer','product')->find($id);
        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }
        return response()->json($sale);
    }

    public function update(Request $request, string $id)
{
    $request->validate([
        'customer_id'     => 'nullable|integer',
        'warehouse_id'    => 'required|integer',
        'sale_date'       => 'required|date',
        'total_amount'    => 'required|numeric',
        'paid_amount'     => 'required|numeric',
        'payment_method'  => 'required|in:cash,card,mobile,bank',
        'status'          => 'required|in:pending,completed,cancelled',
        'items'           => 'required|array|min:1',
        'items.*.product_id' => 'required|integer',
        'items.*.quantity'   => 'required|integer',
        'items.*.unit_price' => 'required|numeric',
        'items.*.subtotal'   => 'required|numeric',
    ]);

    $sale = Sale::find($id);
    if (!$sale) {
        return response()->json(['message' => 'Sale not found'], 404);
    }

    DB::beginTransaction();

    try {
        // Update main sale info
        $sale->update([
            'customer_id'    => $request->customer_id,
            'warehouse_id'   => $request->warehouse_id,
            'sale_date'      => $request->sale_date,
            'total_amount'   => $request->total_amount,
            'paid_amount'    => $request->paid_amount,
            'payment_method' => $request->payment_method,
            'status'         => $request->status,
            'description'    => $request->description,
        ]);

        // Delete existing items
        $sale->items()->delete();

        // Create new items
        foreach ($request->items as $item) {
            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount'   => $item['discount'] ?? 0,
                'tax_percent'=> $item['tax_percent'] ?? 0,
                'tax_amount' => $item['tax_amount'] ?? 0,
                'subtotal'   => $item['subtotal'],
            ]);
        }

        DB::commit();

        return response()->json(['message' => 'Sale updated successfully']);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Failed to update sale', 'details' => $e->getMessage()], 500);
    }
}

    public function destroy(string $id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }

        $sale->items()->delete(); // Delete related items
        $sale->delete();

        return response()->json(['message' => 'Sale deleted successfully']);
    }
}
