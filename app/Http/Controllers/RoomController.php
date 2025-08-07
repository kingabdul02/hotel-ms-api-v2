<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomSearchRequest;
use App\Http\Requests\RoomStoreRequest;
use App\Http\Requests\RoomUpdateRequest;
use App\Http\Resources\RoomCollection;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    use JsonResponse;

    public function index(Request $request): RoomCollection
    {
        $query = Room::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        if ($request->filled('no_of_guests')) {
            $query->where('no_of_guests', '>=', $request->no_of_guests);
        }

        if ($request->filled('no_of_bedrooms')) {
            $query->where('no_of_bedrooms', $request->no_of_bedrooms);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rooms = $query->latest()->paginate($request->get('per_page', 15));

        return new RoomCollection($rooms);
    }

    public function store(RoomStoreRequest $request): RoomResource
    {
        return DB::transaction(function () use ($request) {
            $room = Room::create($request->validated());

            if ($request->has('images')) {
                foreach ($request->images as $image) {
                    $roomImage = new RoomImage();
                    $roomImage->url = $image['url'];
                    $roomImage->room_id = $room->id;
                    $roomImage->save();
                }
            }

            return new RoomResource($room);
        });
    }

    public function show(Request $request, Room $room): RoomResource
    {
        return new RoomResource($room);
    }

    public function update(RoomUpdateRequest $request, Room $room): RoomResource
    {
        $room->update($request->validated());

        if ($request->has('images')) {
            foreach ($request->images as $image) {
                $roomImage = new RoomImage();
                $roomImage->url = $image['url'];
                $roomImage->room_id = $room->id;
                $roomImage->save();
            }
        }

        return new RoomResource($room);
    }

    public function destroy(Request $request, Room $room): Response
    {
        $room->delete();

        return response()->noContent();
    }

    /**
     * @unauthenticated
     */
    public function search(RoomSearchRequest $request)
    {
        $query = Room::where('is_available', true);

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->filled('no_of_guests')) {
            $query->where('no_of_guests', '<=', $request->no_of_guests);
        }

        // if ($request->filled('no_of_bedrooms')) {
        //     $query->where('no_of_bedrooms', $request->no_of_bedrooms);
        // }

        if ($request->filled('check_in_date') && $request->filled('check_out_date')) {
            $checkInDate = $request->check_in_date;
            $checkOutDate = $request->check_out_date;

            $query->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereNull('check_in')
                    ->whereNull('check_out')
                    ->orWhere(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->whereNotBetween('check_in', [$checkInDate, $checkOutDate])
                            ->orWhereNotBetween('check_out', [$checkInDate, $checkOutDate]);
                    });
            });
        }

        $rooms = $query->get();

        return new RoomCollection($rooms);
    }

    /**
     * @unauthenticated
     */
    public function getRoomsByRoomType(RoomType $roomType)
    {
        $rooms = $roomType->rooms;

        if (! $rooms) {
            $this->error('no record found', 404);
        }

        return new RoomCollection($rooms);
    }
}
