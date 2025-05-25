<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryMovementStoreRequest;
use App\Http\Requests\InventoryMovementUpdateRequest;
use App\Http\Resources\InventoryMovementCollection;
use App\Http\Resources\InventoryMovementResource;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryMovementController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    use JsonResponse;

    public function index(Request $request): InventoryMovementCollection
    {
        $inventoryMovements = InventoryMovement::all();

        return new InventoryMovementCollection($inventoryMovements);
    }

    public function store(InventoryMovementStoreRequest $request)
    {
        return $this->inventoryService->processInventory($request->item_id, $request->transaction_type, $request->quantity, $request->transaction_date, $request->remarks);
    }

    public function show(Request $request, InventoryMovement $inventoryMovement): InventoryMovementResource
    {
        return new InventoryMovementResource($inventoryMovement);
    }

    public function update(InventoryMovementUpdateRequest $request, InventoryMovement $inventoryMovement)
    {
        if ($inventoryMovement->transaction_type == 'IN') {
            $inventoryMovement->item->decrement('inventory', $inventoryMovement->quantity);
        } else {
            $inventoryMovement->item->increment('inventory', $inventoryMovement->quantity);
        }

        $inventoryMovement->update($request->all());

        if ($request->transaction_type == 'IN') {
            $inventoryMovement->item->increment('inventory', $request->quantity);
        } else {
            $inventoryMovement->item->decrement('inventory', $request->quantity);
        }

        return new InventoryMovementResource($inventoryMovement);
    }

    public function destroy(Request $request, InventoryMovement $inventoryMovement): Response
    {
        $inventoryMovement->delete();

        return response()->noContent();
    }
}
