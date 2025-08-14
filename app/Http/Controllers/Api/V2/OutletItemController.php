<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreOutletItemRequest;
use App\Http\Resources\OutletItemResource;
use App\Models\OutletItem;
use Illuminate\Http\Request;

class OutletItemController extends Controller
{
    public function index(Request $request)
    {
        $query = OutletItem::query()
            ->when($request->filled('outlet_id'), fn($q) => $q->where('outlet_id', $request->integer('outlet_id')))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->integer('category_id')))
            ->orderBy('name');

        return OutletItemResource::collection($query->paginate(50));
    }

    public function store(StoreOutletItemRequest $request)
    {
        $item = OutletItem::create($request->validated());
        return (new OutletItemResource($item))->response()->setStatusCode(201);
    }

    public function show(OutletItem $outletItem)
    {
        return new OutletItemResource($outletItem);
    }

    public function update(StoreOutletItemRequest $request, OutletItem $outletItem)
    {
        $outletItem->update($request->validated());
        return new OutletItemResource($outletItem);
    }

    public function destroy(OutletItem $outletItem)
    {
        $outletItem->delete();
        return response()->noContent();
    }
}
