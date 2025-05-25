<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelStoreRequest;
use App\Http\Requests\HotelUpdateRequest;
use App\Http\Resources\HotelCollection;
use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HotelController extends Controller
{
    public function index(Request $request): HotelCollection
    {
        $hotels = Hotel::all();

        return new HotelCollection($hotels);
    }

    public function store(HotelStoreRequest $request): HotelResource
    {
        $hotel = Hotel::create($request->validated());

        return new HotelResource($hotel);
    }

    public function show(Request $request, Hotel $hotel): HotelResource
    {
        return new HotelResource($hotel);
    }

    public function update(HotelUpdateRequest $request, Hotel $hotel): HotelResource
    {
        $hotel->update($request->validated());

        return new HotelResource($hotel);
    }

    public function destroy(Request $request, Hotel $hotel): Response
    {
        $hotel->delete();

        return response()->noContent();
    }
}
