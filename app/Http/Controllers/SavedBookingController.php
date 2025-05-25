<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavedBookingStoreRequest;
use App\Http\Requests\SavedBookingUpdateRequest;
use App\Http\Resources\SavedBookingCollection;
use App\Http\Resources\SavedBookingResource;
use App\Models\SavedBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SavedBookingController extends Controller
{
    public function index(Request $request): SavedBookingCollection
    {
        $savedBookings = SavedBooking::all();

        return new SavedBookingCollection($savedBookings);
    }

    public function store(SavedBookingStoreRequest $request): SavedBookingResource
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;

        $savedBooking = SavedBooking::create($data);

        return new SavedBookingResource($savedBooking);
    }

    public function show(Request $request, SavedBooking $savedBooking): SavedBookingResource
    {
        return new SavedBookingResource($savedBooking);
    }

    public function update(SavedBookingUpdateRequest $request, SavedBooking $savedBooking): SavedBookingResource
    {
        $savedBooking->update($request->validated());

        return new SavedBookingResource($savedBooking);
    }

    public function destroy(Request $request, SavedBooking $savedBooking): Response
    {
        $savedBooking->delete();

        return response()->noContent();
    }
}
