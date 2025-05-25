<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderItemStoreRequest;
use App\Http\Requests\PurchaseOrderItemUpdateRequest;
use App\Http\Resources\PurchaseOrderItemCollection;
use App\Http\Resources\PurchaseOrderItemResource;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PurchaseOrderItemController extends Controller
{
    public function index(Request $request): PurchaseOrderItemCollection
    {
        $purchaseOrderItems = PurchaseOrderItem::all();

        return new PurchaseOrderItemCollection($purchaseOrderItems);
    }

    public function store(PurchaseOrderItemStoreRequest $request): PurchaseOrderItemResource
    {
        $purchaseOrderItem = PurchaseOrderItem::create($request->validated());

        return new PurchaseOrderItemResource($purchaseOrderItem);
    }

    public function show(Request $request, PurchaseOrderItem $purchaseOrderItem): PurchaseOrderItemResource
    {
        return new PurchaseOrderItemResource($purchaseOrderItem);
    }

    public function update(PurchaseOrderItemUpdateRequest $request, PurchaseOrderItem $purchaseOrderItem): PurchaseOrderItemResource
    {
        $purchaseOrderItem->update($request->validated());

        return new PurchaseOrderItemResource($purchaseOrderItem);
    }

    public function destroy(Request $request, PurchaseOrderItem $purchaseOrderItem): Response
    {
        $purchaseOrderItem->delete();

        return response()->noContent();
    }
}
