<?php

namespace App\Http\Controllers;

use App\Enums\E_PaymentMethod;
use App\Enums\E_PaymentStatus;
use App\Http\Requests\PaymentEntryStoreRequest;
use App\Http\Requests\PaymentEntryUpdateRequest;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PaymentEntryCollection;
use App\Http\Resources\PaymentEntryResource;
use App\Models\Booking;
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
    ) {
    }

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
                    Log::alert('Payment verification failed: '.$verification['message']);

                    return $this->error('Payment verification failed: '.$verification['message']);
                }

                $paymentEntry = $booking->paymentEntry;
                $paymentEntry->payment_date = $verification['data']['paid_at'] ? Carbon::parse($verification['data']['paid_at'])->format('Y-m-d H:i:s') : null;
                $paymentEntry->payment_status = E_PaymentStatus::SUCCESSFUL;
                $paymentEntry->payment_method = E_PaymentMethod::ONLINE;
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
                $user->notify(new SuccessPayment($booking->booking_id));
            } catch (Exception $e) {
                Log::alert($e);
            }

            return new BookingResource($booking);
        });
    }
}
