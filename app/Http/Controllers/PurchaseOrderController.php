<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderUpdateRequest;
use App\Http\Resources\PurchaseOrderCollection;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    public function index(Request $request): PurchaseOrderCollection
    {
        $purchaseOrders = PurchaseOrder::all();

        return new PurchaseOrderCollection($purchaseOrders);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $totalAmount += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);

                $this->inventoryService->processInventory($item['item_id'], 'IN', $item['quantity'], now(), 'Purchase Order');
            }

            $purchaseOrder->update(['total_amount' => $totalAmount]);

            return new PurchaseOrderResource($purchaseOrder->load('purchaseOrderItems'));
        });
    }

    public function show(Request $request, PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        return new PurchaseOrderResource($purchaseOrder);
    }

    public function update(PurchaseOrderUpdateRequest $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'supplier_id' => $request->supplier_id,
            'order_date' => $request->order_date,
            'total_amount' => 0,
        ]);

        $purchaseOrder->details()->delete();

        $totalAmount = 0;

        foreach ($request->items as $item) {
            $totalPrice = $item['quantity'] * $item['unit_price'];
            $totalAmount += $totalPrice;

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->id,
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $totalPrice,
            ]);
        }

        $purchaseOrder->update(['total_amount' => $totalAmount]);

        return new PurchaseOrderResource($purchaseOrder->load('purchaseOrderItems'));
    }

    public function destroy(Request $request, PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->delete();

        return response()->noContent();
    }
}
