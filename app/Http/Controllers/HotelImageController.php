<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelImageStoreRequest;
use App\Http\Requests\HotelImageUpdateRequest;
use App\Http\Resources\HotelImageCollection;
use App\Http\Resources\HotelImageResource;
use App\Models\HotelImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HotelImageController extends Controller
{
    public function index(Request $request): HotelImageCollection
    {
        $hotelImages = HotelImage::all();

        return new HotelImageCollection($hotelImages);
    }

    public function store(HotelImageStoreRequest $request): HotelImageResource
    {
        $hotelImage = HotelImage::create($request->validated());

        return new HotelImageResource($hotelImage);
    }

    public function show(Request $request, HotelImage $hotelImage): HotelImageResource
    {
        return new HotelImageResource($hotelImage);
    }

    public function update(HotelImageUpdateRequest $request, HotelImage $hotelImage): HotelImageResource
    {
        $hotelImage->update($request->validated());

        return new HotelImageResource($hotelImage);
    }

    public function destroy(Request $request, HotelImage $hotelImage): Response
    {
        $hotelImage->delete();

        return response()->noContent();
    }
}
