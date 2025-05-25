<?php

namespace App\Http\Controllers;

use App\Http\Requests\AmenityStoreRequest;
use App\Http\Requests\AmenityUpdateRequest;
use App\Http\Resources\AmenityCollection;
use App\Http\Resources\AmenityResource;
use App\Models\Amenity;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AmenityController extends Controller
{
    public function index(Request $request): AmenityCollection
    {
        $amenities = Amenity::all();

        return new AmenityCollection($amenities);
    }

    public function store(AmenityStoreRequest $request): AmenityResource
    {
        $amenity = Amenity::create($request->validated());

        return new AmenityResource($amenity);
    }

    public function show(Request $request, Amenity $amenity): AmenityResource
    {
        return new AmenityResource($amenity);
    }

    public function update(AmenityUpdateRequest $request, Amenity $amenity): AmenityResource
    {
        $amenity->update($request->validated());

        return new AmenityResource($amenity);
    }

    public function destroy(Request $request, Amenity $amenity): Response
    {
        $amenity->delete();

        return response()->noContent();
    }
}
