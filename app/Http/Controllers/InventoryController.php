<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryStoreRequest;
use App\Http\Requests\InventoryUpdateRequest;
use App\Http\Resources\InventoryCollection;
use App\Http\Resources\InventoryResource;
use App\Http\Resources\ItemCollection;
use App\Models\Inventory;
use App\Models\Item;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request): ItemCollection
    {
        $inventories = Item::whereHas('inventory')->latest()->get();

        return new ItemCollection($inventories->load('inventory', 'inventoryMovements'));
    }

    public function store(InventoryStoreRequest $request): InventoryResource
    {
        $inventory = Inventory::create($request->validated());

        return new InventoryResource($inventory);
    }

    public function show(Request $request, Inventory $inventory): InventoryResource
    {
        return new InventoryResource($inventory);
    }

    public function update(InventoryUpdateRequest $request, Inventory $inventory): InventoryResource
    {
        $inventory->update($request->validated());

        return new InventoryResource($inventory);
    }

    public function destroy(Request $request, Inventory $inventory): Response
    {
        $inventory->delete();

        return response()->noContent();
    }

    public function useInventory(Request $request) {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer',
            'remarks' => 'required|string',
        ]);

        $inventoryMovement = $this->inventoryService->processInventory(
            $request->item_id,
            'OUT',
            $request->quantity,
            now(),
            $request->remarks
        );

        return $inventoryMovement;
    }
}
