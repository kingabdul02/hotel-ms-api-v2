<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\POS\StoreOutletItemCategoryRequest;
use App\Http\Resources\OutletItemCategoryResource;
use App\Models\OutletItemCategory;
use Illuminate\Http\Request;

class OutletItemCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = OutletItemCategory::with('outlet')
            ->when($request->filled('outlet_id'), fn($q) => $q->where('outlet_id', $request->integer('outlet_id')))
            ->orderBy('name');

        return OutletItemCategoryResource::collection($query->paginate(50));
    }

    public function store(StoreOutletItemCategoryRequest $request)
    {
        $category = OutletItemCategory::create($request->validated());
        return (new OutletItemCategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(OutletItemCategory $outletItemCategory)
    {
        return new OutletItemCategoryResource($outletItemCategory);
    }

    public function update(StoreOutletItemCategoryRequest $request, OutletItemCategory $outletItemCategory)
    {
        $outletItemCategory->update($request->validated());
        return new OutletItemCategoryResource($outletItemCategory);
    }

    public function destroy(OutletItemCategory $outletItemCategory)
    {
        $outletItemCategory->delete();
        return response()->noContent();
    }
}
