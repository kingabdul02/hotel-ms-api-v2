<?php

namespace App\Http\Controllers;

use App\Enums\E_PaymentStatus;
use App\Http\Requests\BookingStoreRequest;
use App\Http\Requests\BookingUpdateRequest;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\PaymentEntry;
use App\Models\Room;
use App\Notifications\SuccessBooking;
use App\Notifications\SuccessCheckIn;
use App\Traits\JsonResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use JsonResponse;

    public function index(Request $request): BookingCollection
    {
        $bookings = Booking::all();

        return new BookingCollection($bookings);
    }

    public function bookRoom(BookingStoreRequest $request)
    {
        Log::alert($request->all());
        $user = auth()->user();

        return DB::transaction(function () use ($request, $user) {
            $room = Room::find($request->room_id);

            if (! $room) {
                return $this->error('Room not found', 404);
            }

            if (! $room->is_available) {
                return $this->error('Room is fully booked!');
            }

            $checkIn = Carbon::parse($request->check_in_date);
            $checkOut = Carbon::parse($request->check_out_date);

            Log::alert($checkIn);
            Log::alert($checkOut);
            // Calculate the difference in days
            $numberOfNights = $checkIn->diffInDays($checkOut);

            $validated = $request->validated();
            $validated['user_id'] = $user->id;
            $validated['booking_id'] = rand(1000, 1000000);
            $validated['no_of_nights'] = $numberOfNights;
            $validated['total_amount'] = $numberOfNights * $room->price;

            $booking = Booking::create($validated);

            $room->is_available = false;
            $room->save();

            $paymentEntry = new PaymentEntry();
            $paymentEntry->booking_id = $booking->id;
            $paymentEntry->payment_amount = $booking->total_amount;
            $paymentEntry->transaction_id = rand(10000000, 9999999999);
            $paymentEntry->save();

            try {
                $user->notify((new SuccessBooking($booking->booking_id)));
            } catch (Exception $e) {
                    Log::alert($e);
                }

            return new BookingResource($booking);
        });
    }

    public function show(Request $request, Booking $booking): BookingResource
    {
        return new BookingResource($booking);
    }

    public function update(BookingUpdateRequest $request, Booking $booking): BookingResource
    {
        $booking->update($request->validated());

        return new BookingResource($booking);
    }

    public function destroy(Request $request, Booking $booking): Response
    {
        $booking->delete();

        return response()->noContent();
    }

    public function getMyBookings(Request $request): BookingCollection
    {
        $bookings = Booking::where('user_id', auth()->user()->id)
            ->where('is_confirmed', true)
            ->get();

        return new BookingCollection($bookings);
    }

    public function getBooking(Request $request)
    {
        $booking = Booking::where('booking_id', $request->booking_id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (! $booking) {
            return $this->error('No record found!');
        }

        return new BookingResource($booking->load('cancelationRequest'));
    }

    public function checkIn(Request $request)
    {
        $booking = Booking::where('booking_id', $request->booking_id)->first();

        if (! $booking) {
            return $this->error('No booking found with ID: '.$request->booking_id, 404);
        }

        return DB::transaction(function () use ($booking) {
            if (! $booking->is_confirmed) {
                return $this->error('Booking must be confirmed before check-in.');
            }

            if ($booking->is_checked_in) {
                return $this->error('Booking is already checked-in!');
            }

            if ($booking->is_checked_out) {
                return $this->error('Booking is checked-out!');
            }

            $booking->is_checked_in = true;
            // if (is_null($booking->check_in_date)) {
            //     $booking->check_in_date = Carbon::now();
            // }
            $booking->check_in_date = Carbon::now();
            $booking->room->check_in = Carbon::now();
            $booking->room->is_available = false;
            $booking->room->save();

            $booking->save();

            try {
                $booking->user->notify(new SuccessCheckIn($booking->booking_id));
            } catch (Exception $e) {
                Log::alert($e);
            }

            return new BookingResource($booking);
        });
    }

    public function checkOut(Request $request)
    {
        $booking = Booking::where('booking_id', $request->booking_id)->first();

        if (! $booking) {
            return $this->error('No booking found with ID: '.$request->booking_id, 404);
        }

        return DB::transaction(function () use ($booking) {
            if (! $booking->is_checked_in) {
                return $this->error('Booking must be checked in before check-out.');
            }

            if ($booking->payment_status !== E_PaymentStatus::PAID->value) {
                return $this->error('Payment not received for this booking!');
            }

            $booking->is_checked_out = true;
            // if (is_null($booking->check_out_date)) {
            //     $booking->check_out_date = Carbon::now();
            // }
            $booking->check_out_date = Carbon::now();
            $booking->is_checked_in = false;
            $booking->room->check_in = null;
            $booking->room->check_out = null;
            $booking->room->is_available = true;
            $booking->room->save();

            $booking->save();

            $booking->user->rooms_booked_counts = + 1;
            $booking->user->save();

            return new BookingResource($booking);
        });
    }
}
