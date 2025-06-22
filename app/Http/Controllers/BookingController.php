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
use App\Models\Coordinator;
use App\Models\CorporateBooking;
use App\Models\CorporateBookingGuest;
use App\Models\Company;
use App\Http\Requests\CorporateBookingRequest;
use App\Http\Resources\CorporateBookingResource;

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
            return $this->error('No booking found with ID: ' . $request->booking_id, 404);
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
            return $this->error('No booking found with ID: ' . $request->booking_id, 404);
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

            $booking->user->rooms_booked_counts = +1;
            $booking->user->save();

            return new BookingResource($booking);
        });
    }

    public function corporateBooking(CorporateBookingRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                if ($request->is_new_company) {
                    $company = Company::create([
                        'name' => $request->company['name'],
                        'email' => $request->company['email'],
                        'phone' => $request->company['phone'],
                        'address' => $request->company['address'],
                        'registration_number' => $request->company['registration_number'] ?? now()->format('YmdHis'),
                    ]);
                } else {
                    $company = Company::find($request->company_id);

                    if (!$company) {
                        throw new Exception('Company not found');
                    }
                }

                $coordinator = Coordinator::create([
                    'company_id' => $company->id,
                    'full_name' => $request->coordinator['full_name'],
                    'email' => $request->coordinator['email'],
                    'phone' => $request->coordinator['phone'],
                    'nin' => $request->coordinator['nin'],
                    'id_card_file' => $request->coordinator['id_card_file'],
                ]);

                $booking = CorporateBooking::create([
                    'company_id' => $company->id,
                    'coordinator_id' => $coordinator->id,
                    'check_in_date' => $request->check_in_date,
                    'check_out_date' => $request->check_out_date,
                    'meal_plan_id' => $request->meal_plan_id,
                    'reservation_code' => rand(1000, 1000000)
                ]);

                foreach ($request->guests as $guest) {
                    CorporateBookingGuest::create([
                        'corporate_booking_id' => $booking->id,
                        'room_id' => $guest['room_id'],
                        'full_name' => $guest['full_name'],
                        'gender' => $guest['gender'] ?? null,
                        'email' => $guest['email'] ?? null,
                        'phone' => $guest['phone'] ?? null,
                    ]);
                }

                $nights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
                $mealPlan = $booking->mealPlan;
                $mealCost = $mealPlan ? ($mealPlan->price_per_day * $nights * $booking->guests->count()) : 0;
                $booking->update(['total_amount' => $mealCost]);
            });

            return $this->success('Corporate booking created successfully');
        } catch (Exception $e) {
            Log::error('Corporate booking failed: ' . $e->getMessage());
            return $this->error($e->getMessage(), 400);
        }
    }

    public function checkInCorporateGuest($guest_id)
    {
        $guest = CorporateBookingGuest::findOrFail($guest_id);

        if ($guest->is_checked_in) {
            return $this->error('Guest already checked in', 400);
        }

        $guest->is_checked_in = true;
        $guest->checked_in_at = now();
        $guest->save();

        $guest->room->check_in = now();
        $guest->room->is_available = false;
        $guest->room->save();

        return $this->success('Guest checked in successfully');
    }

    public function checkOutCorporateGuest($guest_id)
    {
        $guest = CorporateBookingGuest::findOrFail($guest_id);

        if (! $guest->is_checked_in) {
            return $this->error('Guest is not checked in', 400);
        }

        $guest->is_checked_in = false;
        $guest->is_checked_out = true;
        $guest->checked_out_at = now();
        $guest->save();

        $guest->room->check_in = null;
        $guest->room->check_out = null;
        $guest->room->is_available = true;
        $guest->room->save();

        return $this->success('Guest checked out successfully');
    }

    public function listCorporateBookings(Request $request)
    {

        $bookings = CorporateBooking::with(['coordinator', 'guests', 'mealPlan'])->paginate(10);

        return CorporateBookingResource::collection($bookings);
    }

    public function generateBillingReport(Request $request)
    {
        $query = CorporateBooking::with(['company', 'mealPlan'])
            ->withCount('guests')
            ->when($request->filled('from'), fn($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->whereHas('company', function ($subQuery) use ($request) {
                    $subQuery->where('name', 'like', '%' . $request->search . '%');
                });
            });

        $perPage = $request->get('per_page', 15); // default 15 per page
        $bookings = $query->paginate($perPage);

        $report = collect($bookings->items())->map(function ($booking) {
            $nights = \Carbon\Carbon::parse($booking->check_in_date)->diffInDays($booking->check_out_date);
            $mealPlan = $booking->mealPlan;
            $mealPlanCost = $mealPlan ? $mealPlan->price_per_day * $nights * $booking->guests_count : 0;

            return [
                'booking_id' => $booking->id,
                'reservation_code' => $booking->reservation_code,
                'company_name' => optional($booking->company)->name ?? 'N/A',
                'guests_count' => $booking->guests_count,
                'nights' => $nights,
                'meal_plan' => optional($mealPlan)->name,
                'meal_plan_cost' => $mealPlanCost,
                'status' => $booking->status,
                'base_booking_amount' => $booking->total_amount,
                'total_amount_with_meals' => $booking->total_amount + $mealPlanCost,
                'check_in_date' => $booking->check_in_date,
                'check_out_date' => $booking->check_out_date,
                'created_at' => $booking->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $report,
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'per_page' => $bookings->perPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function generateCorporateBill($reservation_code)
    {
        $booking = CorporateBooking::with([
            'company',
            'coordinator',
            'mealPlan',
            'guests.room',
        ])->where('reservation_code', $reservation_code)->first();

        $nights = Carbon::parse($booking->check_in_date)->diffInDays($booking->check_out_date);
        $guestSummaries = [];
        $totalRoomCost = 0;

        foreach ($booking->guests as $guest) {
            $rate = $guest->room->price ?? 0;
            $amount = $rate * $nights;
            $totalRoomCost += $amount;

            $guestSummaries[] = [
                'full_name' => $guest->full_name,
                'room_number' => $guest->room->room_number ?? 'N/A',
                'rate' => $rate,
                'nights' => $nights,
                'amount' => $amount,
            ];
        }

        $mealPlanCost = 0;
        $mealPlan = $booking->mealPlan;

        if ($mealPlan) {
            $mealPlanCost = $mealPlan->price_per_day * $nights * count($booking->guests);
        }

        $total = $totalRoomCost + $mealPlanCost;

        return response()->json([
            'company_name' => $booking->company->name,
            'check_in_date' => $booking->check_in_date,
            'check_out_date' => $booking->check_out_date,
            'nights' => $nights,
            'guests' => $guestSummaries,
            'meal_plan' => $mealPlan ? [
                'name' => $mealPlan->name,
                'rate_per_day' => $mealPlan->price_per_day,
                'total_meal_cost' => $mealPlanCost,
            ] : null,
            'total_accommodation' => $totalRoomCost,
            'grand_total' => $total,
        ]);
    }
}
