<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomTypeStoreRequest;
use App\Http\Requests\RoomTypeUpdateRequest;
use App\Http\Resources\RoomTypeCollection;
use App\Http\Resources\RoomTypeResource;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomTypeController extends Controller
{
    public function index(Request $request): RoomTypeCollection
    {
        $roomTypes = RoomType::all();

        return new RoomTypeCollection($roomTypes);
    }

    public function store(RoomTypeStoreRequest $request): RoomTypeResource
    {
        $roomType = RoomType::create($request->validated());

        return new RoomTypeResource($roomType);
    }

    public function show(Request $request, RoomType $roomType): RoomTypeResource
    {
        return new RoomTypeResource($roomType);
    }

    public function update(RoomTypeUpdateRequest $request, RoomType $roomType): RoomTypeResource
    {
        $roomType->update($request->validated());

        return new RoomTypeResource($roomType);
    }

    public function destroy(Request $request, RoomType $roomType): Response
    {
        $roomType->delete();

        return response()->noContent();
    }

     /**
     * @unauthenticated
     */
    function getRoomType(Request $request) : RoomTypeCollection {
        $roomTypes = RoomType::all();

        return new RoomTypeCollection($roomTypes);
    }
}
