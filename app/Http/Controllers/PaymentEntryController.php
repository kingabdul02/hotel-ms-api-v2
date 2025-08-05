<?php

namespace App\Http\Controllers;

use App\Enums\E_PaymentMethod;
use App\Enums\E_PaymentStatus;
use App\Enums\E_BookingType;
use App\Enums\E_Portal;
use App\Http\Requests\CompletePaymentRequest;
use App\Http\Requests\PaymentEntryStoreRequest;
use App\Http\Requests\PaymentEntryUpdateRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentEntryCollection;
use App\Http\Resources\PaymentEntryResource;
use App\Http\Resources\CorporateBookingResource;
use App\Models\Booking;
use App\Models\CorporateBooking;
use App\Models\PaymentEntry;
use App\Models\PaymentEntryTransaction;
use App\Notifications\SuccessPayment;
use App\Services\PaymentService;
use App\Traits\JsonResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentEntryController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {}

    use JsonResponse;

    public function index(Request $request): PaymentEntryCollection
    {
        $paymentEntries = PaymentEntry::all();

        return new PaymentEntryCollection($paymentEntries);
    }

    // public function store(PaymentEntryStoreRequest $request): PaymentEntryResource
    // {
    //     $paymentEntry = PaymentEntry::create($request->validated());

    //     return new PaymentEntryResource($paymentEntry);
    // }

    public function show(Request $request, PaymentEntry $paymentEntry): PaymentEntryResource
    {
        return new PaymentEntryResource($paymentEntry);
    }

    // public function update(PaymentEntryUpdateRequest $request, PaymentEntry $paymentEntry): PaymentEntryResource
    // {
    //     $paymentEntry->update($request->validated());

    //     return new PaymentEntryResource($paymentEntry);
    // }

    // public function destroy(Request $request, PaymentEntry $paymentEntry): Response
    // {
    //     $paymentEntry->delete();

    //     return response()->noContent();
    // }

    public function makePament(Request $request)
    {

        $booking = Booking::where('booking_id', $request->booking_id)->first();

        if (! $booking) {
            return $this->error('No booking record found', 404);
        }

        return DB::transaction(function () use ($booking, $request) {
            $booking->room->is_available = false;
            $booking->room->check_in = $booking->check_in_date;
            $booking->room->check_out = $booking->check_out_date;
            $booking->room->save();

            $user = auth()->user();

            $booking->is_confirmed = true;
            $booking->payment_status = E_PaymentStatus::PAID;
            $booking->user_id = $user->id;
            $booking->save();

            if (! $booking->paymentEntry) {
                return $this->error('Payment entry not found for the booking', 404);
            }

            if ($request['payment_provider'] === 'Paystack') {
                $verification = $this->paymentService->verifyPaystackPayment($request['reference']);
                Log::alert($verification);

                if ($verification['status'] !== true) {
                    Log::alert('Payment verification failed: ' . $verification['message']);

                    return $this->error('Payment verification failed: ' . $verification['message']);
                }

                $paymentEntry = $booking->paymentEntry;
                $paymentEntry->payment_date = $verification['data']['paid_at'] ? Carbon::parse($verification['data']['paid_at'])->format('Y-m-d H:i:s') : null;
                $paymentEntry->payment_status = E_PaymentStatus::SUCCESSFUL;
                $paymentEntry->payment_method = E_PaymentMethod::ONLINE;
                $paymentEntry->booking_type = E_BookingType::INDIVIDUAL;
                $paymentEntry->portal = E_Portal::PUBLIC;
                $paymentEntry->save();

                Log::alert($request->all());
                $paymentDate = Carbon::parse($verification['data']['paid_at'])->format('Y-m-d H:i:s');
                $transaction = new PaymentEntryTransaction();
                $transaction->payment_provider = $request['payment_provider'];
                $transaction->reference = $request['reference'];
                $transaction->transaction = $request['transaction'];
                $transaction->amount = $request['amount'];
                $transaction->status = $verification['data']['status'];
                $transaction->message = $verification['message'];
                $transaction->payment_date = $verification['data']['paid_at'] ? $verification['data']['paid_at'] : null;
                $transaction->channel = $verification['data']['channel'] ? $verification['data']['channel'] : null;
                $transaction->host_trx_ref = $request['host_trx_ref'];
                $transaction->raw_data = json_encode($request['raw_data']);
                $transaction->payment_entry_id = $paymentEntry->id;
                $transaction->save();
            }

            try {
                $booking_user = $booking->user;
                if ($booking_user) {
                    $booking_user->notify(new SuccessPayment($booking->booking_id));
                }
            } catch (Exception $e) {
                Log::alert($e);
            }

            return new BookingResource($booking);
        });
    }

    /**
     * Complete payment for a booking (Admin endpoint for offline payments)
     */
    public function completePayment(CompletePaymentRequest $request)
    {
        $booking = Booking::where('booking_id', $request->booking_id)->first();

        if (!$booking) {
            return $this->error('No booking record found', 404);
        }

        return DB::transaction(function () use ($booking, $request) {
            // Update room availability
            $booking->room->is_available = false;
            $booking->room->check_in = $booking->check_in_date;
            $booking->room->check_out = $booking->check_out_date;
            $booking->room->save();

            // Update booking status
            $booking->is_confirmed = true;
            $booking->payment_status = E_PaymentStatus::PAID;
            $booking->save();

            if (!$booking->paymentEntry) {
                return $this->error('Payment entry not found for the booking', 404);
            }

            // Map payment method from request to enum
            $paymentMethodMapping = [
                'cash' => E_PaymentMethod::CASH,
                'pos' => E_PaymentMethod::POS,
                'transfer' => E_PaymentMethod::TRANSFER,
            ];

            $paymentMethod = $paymentMethodMapping[$request->payment_method];

            // Update payment entry
            $paymentEntry = $booking->paymentEntry;
            $paymentEntry->payment_date = now();
            $paymentEntry->payment_status = E_PaymentStatus::SUCCESSFUL;
            $paymentEntry->payment_method = $paymentMethod;
            $paymentEntry->booking_type = E_BookingType::INDIVIDUAL;
            $paymentEntry->portal = E_Portal::ASSISTED;
            $paymentEntry->save();

            // Create payment transaction record
            $transaction = new PaymentEntryTransaction();
            $transaction->payment_provider = 'admin';
            $transaction->transaction = time();
            $transaction->reference = 'ADMIN_' . time() . '_' . $booking->booking_id;
            $transaction->amount = $paymentEntry->payment_amount;
            $transaction->status = 'success';
            $transaction->message = 'Payment completed by admin using ' . $request->payment_method;
            $transaction->payment_date = now();
            $transaction->payment_entry_id = $paymentEntry->id;
            $transaction->raw_data = json_encode($request->all());
            $transaction->host_trx_ref = 'ADMIN_' . time() . '_' . $booking->booking_id;
            $transaction->save();

            try {
                $booking_user = $booking->user;
                if ($booking_user) {
                    $booking_user->notify(new SuccessPayment($booking->booking_id));
                }
            } catch (Exception $e) {
                Log::alert($e);
            }

            return $this->success(new BookingResource($booking));
        });
    }

    public function completeCorporatePayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string|in:cash,pos,transfer',
            'reservation_code' => 'required|string|exists:corporate_bookings,reservation_code',
        ]);

        $corporateBooking = CorporateBooking::where('reservation_code', $request->reservation_code)->first();

        if (!$corporateBooking) {
            return $this->error('No corporate booking record found', 404);
        }

        return DB::transaction(function () use ($corporateBooking, $request) {
            // Update corporate booking status
            $corporateBooking->payment_status = E_PaymentStatus::PAID;
            $corporateBooking->save();

            // Map payment method from request to enum
            $paymentMethodMapping = [
                'cash' => E_PaymentMethod::CASH,
                'pos' => E_PaymentMethod::POS,
                'transfer' => E_PaymentMethod::TRANSFER,
            ];

            $paymentMethod = $paymentMethodMapping[$request->payment_method];

            // Create or update payment entry
            $paymentEntry = new PaymentEntry();
            $paymentEntry->payment_date = now();
            $paymentEntry->payment_status = E_PaymentStatus::SUCCESSFUL;
            $paymentEntry->payment_method = $paymentMethod;
            $paymentEntry->payment_amount = $corporateBooking->total_amount;
            $paymentEntry->transaction_id = 'ADMIN_' . time() . '_' . $corporateBooking->reservation_code;
            $paymentEntry->booking_type = E_BookingType::CORPORATE;
            $paymentEntry->portal = E_Portal::ASSISTED;
            $paymentEntry->save();

            // Create payment transaction record
            $transaction = new PaymentEntryTransaction();
            $transaction->payment_provider = 'admin';
            $transaction->transaction = time();
            $transaction->reference = 'ADMIN_' . time() . '_' . $corporateBooking->reservation_code;
            $transaction->amount = $paymentEntry->payment_amount;
            $transaction->status = 'success';
            $transaction->message = 'Corporate payment completed by admin using ' . $request->payment_method;
            $transaction->payment_date = now();
            $transaction->payment_entry_id = $paymentEntry->id;
            $transaction->raw_data = json_encode($request->all());
            $transaction->host_trx_ref = 'ADMIN_' . time() . '_' . $corporateBooking->reservation_code;
            $transaction->save();

            try {
                $coordinator = $corporateBooking->coordinator;
                if ($coordinator) {
                    $coordinator->notify(new SuccessPayment($corporateBooking->reservation_code));
                }
            } catch (Exception $e) {
                Log::alert($e);
            }

            // $grandTotal = $corporateBooking->guests->sum(function ($guest) use ($corporateBooking) {
            //     $nights = Carbon::parse($corporateBooking->check_in_date)->diffInDays($corporateBooking->check_out_date);
            //     $roomCost = $guest->room->price * $nights;
            //     return $roomCost;
            // }) + ($corporateBooking->mealPlan ? $corporateBooking->mealPlan->price_per_day * $corporateBooking->guests->count() * $nights : 0) + $corporateBooking->halls->sum('amount');

            // if ($request->amount_paid != $grandTotal) {
            //     return $this->error('Payment amount does not match the grand total.', 400);
            // }

            return $this->success(new CorporateBookingResource($corporateBooking));
        });
    }
}
