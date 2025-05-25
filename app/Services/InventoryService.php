<?php

namespace App\Services;

use App\Http\Resources\InventoryMovementResource;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Traits\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    use JsonResponse;

    public function processInventory($item_id, $transaction_type, $quantity, $transaction_date, $remarks)
    {
        return DB::transaction(function () use ($item_id, $transaction_type, $quantity, $transaction_date, $remarks) {
            $item = Item::find($item_id);
            $inventory = Inventory::where('item_id', $item_id)->first();

            if ($transaction_type == 'OUT') {
                if (! $inventory || $inventory->quantity < $quantity) {
                    return response()->json(['error' => 'Not enough inventory for this stocking.'], 401);
                }
            }

            $inventoryMovement = InventoryMovement::create([
                'item_id' => $item_id,
                'transaction_type' => $transaction_type,
                'quantity' => $quantity,
                'transaction_date' => $transaction_date,
                'remarks' => $remarks,
            ]);

            Log::alert($inventoryMovement);

            if ($transaction_type == 'IN') {
                if (! $inventory) {
                    Inventory::create([
                        'item_id' => $item_id,
                        'quantity' => $quantity,
                        'last_updated' => now(),
                    ]);
                } else {
                    $inventory->increment('quantity', $quantity);
                    $inventory->update(['last_updated' => now()]);
                }
            } else {
                $inventory->decrement('quantity', $quantity);
                $inventory->update(['last_updated' => now()]);
            }

            return new InventoryMovementResource($inventoryMovement);
        });
    }
}
