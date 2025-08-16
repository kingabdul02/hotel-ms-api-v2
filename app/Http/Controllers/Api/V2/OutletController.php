<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $query = Outlet::query();
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $outlets = $query->paginate($request->input('per_page', 15));
        return OutletResource::collection($outlets);
    }

    public function store(StoreOutletRequest $request)
    {
        $outlet = Outlet::create($request->validated());
        return new OutletResource($outlet);
    }

    public function show(Outlet $outlet)
    {
        $outlet->load('categories', 'items');
        return new OutletResource($outlet);
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet)
    {
        $outlet->update($request->validated());
        return new OutletResource($outlet);
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->delete();
        return response()->noContent();
    }
}
