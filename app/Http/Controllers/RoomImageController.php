<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomImageStoreRequest;
use App\Http\Requests\RoomImageUpdateRequest;
use App\Http\Resources\RoomImageCollection;
use App\Http\Resources\RoomImageResource;
use App\Models\RoomImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomImageController extends Controller
{
    public function index(Request $request): RoomImageCollection
    {
        $roomImages = RoomImage::all();

        return new RoomImageCollection($roomImages);
    }

    public function store(RoomImageStoreRequest $request): RoomImageResource
    {
        $roomImage = RoomImage::create($request->validated());

        return new RoomImageResource($roomImage);
    }

    public function show(Request $request, RoomImage $roomImage): RoomImageResource
    {
        return new RoomImageResource($roomImage);
    }

    public function update(RoomImageUpdateRequest $request, RoomImage $roomImage): RoomImageResource
    {
        $roomImage->update($request->validated());

        return new RoomImageResource($roomImage);
    }

    public function destroy(Request $request, RoomImage $roomImage): Response
    {
        $roomImage->delete();

        return response()->noContent();
    }
}
