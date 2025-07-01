<?php

namespace App\Http\Controllers\Api\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stock\Stocktransfer;
use App\Models\Stock\Stock;
use Illuminate\Support\Facades\DB;

class StocktransferController extends Controller
{
    
    public function index()
    {
        $stocktransfers=Stocktransfer::with('fromWarehouse','toWarehouse','product')->get();
        return response()->json($stocktransfers);
    }

  
    public function store(Request $request)
        {
            try {
                $validated = $request->validate([
                    'product_id' => 'required|exists:products,id',
                    'from_warehouse_id' => 'required|exists:warehouses,id',
                    'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                    'quantity' => 'required|integer|min:1',
                    'transfer_date' => 'required|date',
                    'status' => 'required|in:inprogress,completed',
                    'description' => 'nullable|string',
                ]);
            } catch (\Exception $e) {
                \Log::error('Stock Transfer Validation Error: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 422);
            }

            DB::transaction(function () use ($validated) {
                // Create transfer record
                $transfer = Stocktransfer::create($validated);

                if ($validated['status'] === 'completed') {
                    // Source warehouse stock check and decrement
                    $fromStock = Stock::firstOrCreate([
                        'product_id' => $validated['product_id'],
                        'warehouse_id' => $validated['from_warehouse_id'],
                    ]);

                    if ($fromStock->quantity < $validated['quantity']) {
                        abort(400, 'Stock not sufficient in source warehouse.');
                    }

                    $fromStock->decrement('quantity', $validated['quantity']);

                    // Destination warehouse stock increment
                    $toStock = Stock::firstOrCreate([
                        'product_id' => $validated['product_id'],
                        'warehouse_id' => $validated['to_warehouse_id'],
                    ]);

                    $toStock->increment('quantity', $validated['quantity']);
                }
            });

            return response()->json(['message' => 'Stock transfer saved successfully'], 201);
        }

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:inprogress,completed',
    ]);

    $stockTransfer = Stocktransfer::findOrFail($id);
    $oldStatus = $stockTransfer->status;
    $newStatus = $request->status;

    if ($oldStatus === $newStatus) {
        return response()->json(['message' => 'Status is already ' . $newStatus]);
    }

    try {
    DB::transaction(function () use ($stockTransfer, $oldStatus, $newStatus) {
        \Log::info("Updating stock transfer ID {$stockTransfer->id} from {$oldStatus} to {$newStatus}");

        if ($oldStatus === 'inprogress' && $newStatus === 'completed') {
            $fromStock = Stock::firstOrCreate([
                'product_id' => $stockTransfer->product_id,
                'warehouse_id' => $stockTransfer->from_warehouse_id,
            ]);

            \Log::info("FromStock quantity before decrement: {$fromStock->quantity}");

            if ($fromStock->quantity < $stockTransfer->quantity) {
                throw new \Exception('Stock not sufficient in source warehouse.');
            }

            $fromStock->decrement('quantity', $stockTransfer->quantity);
            \Log::info("FromStock quantity after decrement: " . Stock::find($fromStock->id)->quantity);

            $toStock = Stock::firstOrCreate([
                'product_id' => $stockTransfer->product_id,
                'warehouse_id' => $stockTransfer->to_warehouse_id,
            ]);

            $toStock->increment('quantity', $stockTransfer->quantity);
            \Log::info("ToStock quantity after increment: " . Stock::find($toStock->id)->quantity);

        } elseif ($oldStatus === 'completed' && $newStatus === 'inprogress') {
            $fromStock = Stock::firstOrCreate([
                'product_id' => $stockTransfer->product_id,
                'warehouse_id' => $stockTransfer->from_warehouse_id,
            ]);

            $fromStock->increment('quantity', $stockTransfer->quantity);
            \Log::info("FromStock quantity after increment: " . Stock::find($fromStock->id)->quantity);

            $toStock = Stock::firstOrCreate([
                'product_id' => $stockTransfer->product_id,
                'warehouse_id' => $stockTransfer->to_warehouse_id,
            ]);

            if ($toStock->quantity < $stockTransfer->quantity) {
                throw new \Exception('Stock not sufficient in destination warehouse to revert.');
            }

            $toStock->decrement('quantity', $stockTransfer->quantity);
            \Log::info("ToStock quantity after decrement: " . Stock::find($toStock->id)->quantity);
        }

        $stockTransfer->status = $newStatus;
        $stockTransfer->save();

        \Log::info("Stock transfer ID {$stockTransfer->id} status updated to {$newStatus}");
    });
} catch (\Exception $e) {
    Log::error('Stock status update failed: ' . $e->getMessage());
    return response()->json(['error' => $e->getMessage()], 400);
}

    return response()->json(['message' => 'Status updated successfully']);
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
