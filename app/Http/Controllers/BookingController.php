<?php

namespace App\Http\Controllers;

use App\Enums\E_BookingStatus;
use App\Enums\E_PaymentStatus;
use App\Http\Requests\BookingStoreRequest;
use App\Http\Requests\BookingUpdateRequest;
use App\Http\Resources\BookingCollection;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\PaymentEntry;
use App\Models\Room;
use App\Models\User;
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
use App\Models\CorporateBookingHall;
use App\Models\Company;
use App\Http\Requests\CorporateBookingRequest;
use App\Http\Requests\CorporateBookingUpdateRequest;
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

            // Calculate the difference in days
            $numberOfNights = $checkIn->diffInDays($checkOut);

            $validated = $request->validated();
            $validated['user_id'] = $user->id;
            $validated['booking_id'] = rand(1000, 1000000);
            $validated['no_of_nights'] = $numberOfNights;

            // Base total before discounts
            $baseTotal = $numberOfNights * $room->price;

            // Compute discount if provided
            $discountType = $request->input('discount_type');
            $discountValue = (float) $request->input('discount_value', 0);

            $discountAmount = 0.0;
            if ($discountType && $discountValue > 0) {
                if ($discountType === 'percent') {
                    // Treat values > 100 as 100%; clamp to [0,100]
                    $percent = max(0, min(100, $discountValue));
                    $discountAmount = round(($percent / 100) * $baseTotal, 2);
                } elseif ($discountType === 'amount') {
                    $discountAmount = round($discountValue, 2);
                }
            }

            // Final totals after discount, not below zero
            $totalAfterDiscount = max(0, round($baseTotal - $discountAmount, 2));

            $validated['total_amount'] = $totalAfterDiscount;
            $validated['paid_amount'] = 0;
            $validated['balance'] = $totalAfterDiscount;

            // Persist discount metadata if the Booking model allows it in fillable
            // (Even if not, they will be ignored; keeping here for potential later migration)
            if ($discountType) {
                $validated['discount_type'] = $discountType;
            }
            if ($discountValue) {
                $validated['discount_value'] = $discountValue;
            }

            $booking = Booking::create($validated);

            $room->is_available = false;
            $room->save();

            $paymentEntry = new PaymentEntry();
            $paymentEntry->booking_id = $booking->id;
            $paymentEntry->payment_amount = $booking->total_amount;
            $paymentEntry->transaction_id = rand(10000000, 9999999999);
            $paymentEntry->save();

            try {
                $authUser = User::find($user->id);
                if ($authUser) {
                    $authUser->notify((new SuccessBooking($booking->booking_id)));
                }
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
            $booking->status = E_BookingStatus::CHECKED_IN->value;
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
            $booking->status = E_BookingStatus::CHECKED_OUT->value;
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
                    'reservation_code' => rand(1000, 1000000),
                    'expected_guests' => $request->expected_guests
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

                // Handle halls if provided
                if ($request->has('halls') && is_array($request->halls)) {
                    foreach ($request->halls as $hall) {
                        CorporateBookingHall::create([
                            'corporate_booking_id' => $booking->id,
                            'hall_id' => $hall['hall_id'],
                            'hall_name' => $hall['hall_name'],
                            'hall_price' => $hall['hall_price'],
                            'start_date' => $hall['start_date'],
                            'end_date' => $hall['end_date'],
                            'amount' => $hall['amount'],
                        ]);
                    }
                }

                $nights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
                $mealPlan = $booking->mealPlan;
                $mealCost = $mealPlan ? ($mealPlan->price_per_day * $nights * $booking->guests->count()) : 0;

                // Calculate total halls cost
                $hallsCost = $booking->halls->sum('amount');

                $roomCost = $booking->guests->sum(function ($guest) use ($nights) {
                    return $guest->room->price * $nights;
                });

                $totalAmount = $mealCost + $hallsCost + $roomCost;
                $booking->update(['total_amount' => $totalAmount]);
            });

            return $this->success('Corporate booking created successfully');
        } catch (Exception $e) {
            Log::error('Corporate booking failed: ' . $e->getMessage());
            return $this->error($e->getMessage(), 400);
        }
    }

    public function checkInCorporateGuest($guest_id)
    {
        $guest = CorporateBookingGuest::with('booking')->findOrFail($guest_id);

        if ($guest->is_checked_in) {
            return $this->error('Guest already checked in', 400);
        }

        // Check and update corporate booking status if not checked in
        $booking = $guest->booking;

        // if ($booking && $booking->status === 'pending') {
        $booking->status = E_BookingStatus::CHECKED_IN->value;
        $booking->check_in_date = now();
        $booking->save();
        // }

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

    public function checkInCorporateBooking($corporate_booking_id)
    {
        try {
            $corporateBooking = CorporateBooking::with('guests.room')->findOrFail($corporate_booking_id);

            // Check if corporate booking is already checked in
            if ($corporateBooking->status === 'checked_in') {
                return $this->error('Corporate booking is already checked in', 400);
            }

            // Check if corporate booking has any guests
            if ($corporateBooking->guests->isEmpty()) {
                return $this->error('No guests found for this corporate booking', 400);
            }

            $checkedInGuests = 0;
            $alreadyCheckedInGuests = 0;
            $errors = [];

            DB::transaction(function () use ($corporateBooking, &$checkedInGuests, &$alreadyCheckedInGuests, &$errors) {
                foreach ($corporateBooking->guests as $guest) {
                    if ($guest->is_checked_in) {
                        $alreadyCheckedInGuests++;
                        continue;
                    }

                    try {
                        // Check in the guest
                        $guest->is_checked_in = true;
                        $guest->checked_in_at = now();
                        $guest->save();

                        // Update room status
                        if ($guest->room) {
                            $guest->room->check_in = now();
                            $guest->room->is_available = false;
                            $guest->room->save();
                        }

                        $checkedInGuests++;
                    } catch (Exception $e) {
                        $errors[] = "Failed to check in guest {$guest->id}: " . $e->getMessage();
                    }
                }

                // Update corporate booking status
                $corporateBooking->status = E_BookingStatus::CHECKED_IN->value;
                $corporateBooking->check_in_date = now();
                $corporateBooking->save();
            });

            $message = "Corporate booking checked in successfully. ";
            $message .= "Checked in: {$checkedInGuests} guests. ";
            if ($alreadyCheckedInGuests > 0) {
                $message .= "Already checked in: {$alreadyCheckedInGuests} guests. ";
            }
            if (!empty($errors)) {
                $message .= "Errors: " . implode(', ', $errors);
            }

            return $this->success([
                'message' => $message,
                'corporate_booking_id' => $corporate_booking_id,
                'checked_in_guests' => $checkedInGuests,
                'already_checked_in_guests' => $alreadyCheckedInGuests,
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            Log::error('Corporate booking check-in failed: ' . $e->getMessage());
            return $this->error('Failed to check in corporate booking: ' . $e->getMessage(), 400);
        }
    }

    public function checkOutCorporateBooking($corporate_booking_id)
    {
        try {
            $corporateBooking = CorporateBooking::with('guests.room')->findOrFail($corporate_booking_id);

            // Check if corporate booking is already checked out
            if ($corporateBooking->status === 'checked_out') {
                return $this->error('Corporate booking is already checked out', 400);
            }

            // Check if corporate booking has any guests
            if ($corporateBooking->guests->isEmpty()) {
                return $this->error('No guests found for this corporate booking', 400);
            }

            $checkedOutGuests = 0;
            $notCheckedInGuests = 0;
            $alreadyCheckedOutGuests = 0;
            $errors = [];

            DB::transaction(function () use ($corporateBooking, &$checkedOutGuests, &$notCheckedInGuests, &$alreadyCheckedOutGuests, &$errors) {
                foreach ($corporateBooking->guests as $guest) {
                    if (!$guest->is_checked_in) {
                        $notCheckedInGuests++;
                        continue;
                    }

                    if ($guest->is_checked_out) {
                        $alreadyCheckedOutGuests++;
                        continue;
                    }

                    try {
                        // Check out the guest
                        $guest->is_checked_in = false;
                        $guest->is_checked_out = true;
                        $guest->checked_out_at = now();
                        $guest->save();

                        // Update room status
                        if ($guest->room) {
                            $guest->room->check_in = null;
                            $guest->room->check_out = null;
                            $guest->room->is_available = true;
                            $guest->room->save();
                        }

                        $checkedOutGuests++;
                    } catch (Exception $e) {
                        $errors[] = "Failed to check out guest {$guest->id}: " . $e->getMessage();
                    }
                }

                // Update corporate booking status
                $corporateBooking->status = E_BookingStatus::CHECKED_OUT->value;
                $corporateBooking->check_out_date = now();
                $corporateBooking->save();
            });

            $message = "Corporate booking checked out successfully. ";
            $message .= "Checked out: {$checkedOutGuests} guests. ";
            if ($notCheckedInGuests > 0) {
                $message .= "Not checked in: {$notCheckedInGuests} guests. ";
            }
            if ($alreadyCheckedOutGuests > 0) {
                $message .= "Already checked out: {$alreadyCheckedOutGuests} guests. ";
            }
            if (!empty($errors)) {
                $message .= "Errors: " . implode(', ', $errors);
            }

            return $this->success([
                'message' => $message,
                'corporate_booking_id' => $corporate_booking_id,
                'checked_out_guests' => $checkedOutGuests,
                'not_checked_in_guests' => $notCheckedInGuests,
                'already_checked_out_guests' => $alreadyCheckedOutGuests,
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            Log::error('Corporate booking check-out failed: ' . $e->getMessage());
            return $this->error('Failed to check out corporate booking: ' . $e->getMessage(), 400);
        }
    }

    public function listCorporateBookings(Request $request)
    {
        $query = CorporateBooking::with(['company', 'coordinator', 'guests', 'mealPlan', 'halls']);

        // Search by company name or coordinator name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('company', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('coordinator', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%");
                })->orWhere('reservation_code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment_status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by check_in_date range
        if ($request->filled('check_in_from')) {
            $query->whereDate('check_in_date', '>=', $request->check_in_from);
        }
        if ($request->filled('check_in_to')) {
            $query->whereDate('check_in_date', '<=', $request->check_in_to);
        }

        // Filter by check_out_date range
        if ($request->filled('check_out_from')) {
            $query->whereDate('check_out_date', '>=', $request->check_out_from);
        }
        if ($request->filled('check_out_to')) {
            $query->whereDate('check_out_date', '<=', $request->check_out_to);
        }

        // Filter by booking created_at date range
        if ($request->filled('booking_date_from')) {
            $query->whereDate('created_at', '>=', $request->booking_date_from);
        }
        if ($request->filled('booking_date_to')) {
            $query->whereDate('created_at', '<=', $request->booking_date_to);
        }

        $perPage = $request->get('per_page', 10);

        $bookings = $query->latest()->paginate($perPage);

        return CorporateBookingResource::collection($bookings);
    }

    public function generateBillingReport(Request $request)
    {
        $query = CorporateBooking::with(['company', 'mealPlan', 'halls'])
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
            $hallsCost = $booking->halls->sum('amount');

            return [
                'booking_id' => $booking->id,
                'reservation_code' => $booking->reservation_code,
                'company_name' => optional($booking->company)->name ?? 'N/A',
                'guests_count' => $booking->guests_count,
                'nights' => $nights,
                'meal_plan' => optional($mealPlan)->name,
                'meal_plan_cost' => $mealPlanCost,
                'halls_cost' => $hallsCost,
                'status' => $booking->status,
                'base_booking_amount' => $booking->total_amount,
                'total_amount_with_meals_and_halls' => $booking->total_amount + $mealPlanCost + $hallsCost,
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
            'halls',
        ])->where('reservation_code', $reservation_code)->first();

        if (! $booking) {
            return $this->error('No booking found with reservation code: ' . $reservation_code, 404);
        }

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

        // Calculate halls cost
        $hallsCost = $booking->halls->sum('amount');
        $hallsDetails = $booking->halls->map(function ($hall) {
            return [
                'hall_name' => $hall->hall_name,
                'hall_price' => $hall->hall_price,
                'start_date' => $hall->start_date,
                'end_date' => $hall->end_date,
                'amount' => $hall->amount,
            ];
        });

        $total = $totalRoomCost + $mealPlanCost + $hallsCost;

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
            'halls' => $hallsDetails->toArray(),
            'total_accommodation' => $totalRoomCost,
            'total_halls_cost' => $hallsCost,
            'payment_status' => $booking->payment_status,
            'grand_total' => $total,
        ]);
    }

    public function getCorporateBookingDetails($corporate_booking_id)
    {
        $booking = CorporateBooking::with(['company', 'coordinator', 'guests.room', 'mealPlan', 'halls'])
            ->findOrFail($corporate_booking_id);

        return new CorporateBookingResource($booking);
    }

    public function updateCorporateBooking(CorporateBookingUpdateRequest $request, $corporate_booking_id)
    {
        try {
            DB::transaction(function () use ($request, $corporate_booking_id) {
                $booking = CorporateBooking::with(['guests', 'coordinator', 'company'])->findOrFail($corporate_booking_id);

                // Update or fetch company
                if ($request->is_new_company) {
                    $company = $booking->company;
                    $company->update([
                        'name' => $request->company['name'],
                        'email' => $request->company['email'],
                        'phone' => $request->company['phone'],
                        'address' => $request->company['address'],
                    ]);
                } else {
                    $company = Company::where('registration_number', $request->registration_number)->first();
                    if (!$company) {
                        throw new Exception('Company not found');
                    }

                    $booking->company_id = $company->id;
                }

                // Update coordinator
                $coordinator = $booking->coordinator;
                $coordinator->update([
                    'company_id' => $company->id,
                    'full_name' => $request->coordinator['full_name'],
                    'email' => $request->coordinator['email'],
                    'phone' => $request->coordinator['phone'],
                    'nin' => $request->coordinator['nin'],
                    'id_card_file' => $request->coordinator['id_card_file'],
                ]);

                // Update booking
                $booking->update([
                    'check_in_date' => $request->check_in_date,
                    'check_out_date' => $request->check_out_date,
                    'meal_plan_id' => $request->meal_plan_id,
                    'company_id' => $company->id,
                    'coordinator_id' => $coordinator->id,
                ]);

                // Remove old guests and re-insert new ones
                $booking->guests()->delete();

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

                // Remove old halls and re-insert new ones
                $booking->halls()->delete();

                if ($request->has('halls') && is_array($request->halls)) {
                    foreach ($request->halls as $hall) {
                        CorporateBookingHall::create([
                            'corporate_booking_id' => $booking->id,
                            'hall_id' => $hall['hall_id'],
                            'hall_name' => $hall['hall_name'],
                            'hall_price' => $hall['hall_price'],
                            'start_date' => $hall['start_date'],
                            'end_date' => $hall['end_date'],
                            'amount' => $hall['amount'],
                        ]);
                    }
                }

                $booking->expected_guests = $request->expected_guests ? $request->expected_guests : count($request->guests);

                // Recalculate total amount
                $nights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
                $mealPlan = $booking->mealPlan()->first();
                $mealCost = $mealPlan ? ($mealPlan->price_per_day * $nights * $booking->guests()->count()) : 0;

                // Calculate halls cost
                $hallsCost = $booking->halls()->sum('amount');

                $roomCost = $booking->guests->sum(function ($guest) use ($nights) {
                    return $guest->room->price * $nights;
                });

                $totalAmount = $mealCost + $hallsCost + $roomCost;
                $booking->update(['total_amount' => $totalAmount]);
            });

            return $this->success('Corporate booking updated successfully');
        } catch (Exception $e) {
            Log::error('Corporate booking update failed: ' . $e->getMessage());
            return $this->error($e->getMessage(), 400);
        }
    }
}
