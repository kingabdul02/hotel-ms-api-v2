<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemStoreRequest;
use App\Http\Requests\ItemUpdateRequest;
use App\Http\Resources\ItemCollection;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemController extends Controller
{
    public function index(Request $request): ItemCollection
    {
        $items = Item::all();

        return new ItemCollection($items);
    }

    public function store(ItemStoreRequest $request): ItemResource
    {
        $item = Item::create($request->validated());

        return new ItemResource($item);
    }

    public function show(Request $request, Item $item): ItemResource
    {
        return new ItemResource($item);
    }

    public function update(ItemUpdateRequest $request, Item $item): ItemResource
    {
        $item->update($request->validated());

        return new ItemResource($item);
    }

    public function destroy(Request $request, Item $item): Response
    {
        $item->delete();

        return response()->noContent();
    }
}
