<?php

namespace App\Http\Controllers;

use App\Enums\E_PaymentStatus;
use App\Enums\E_Status;
use App\Http\Requests\CancelationRequestStoreRequest;
use App\Http\Requests\CancelationRequestUpdateRequest;
use App\Http\Resources\CancelationRequestCollection;
use App\Http\Resources\CancelationRequestResource;
use App\Models\Booking;
use App\Models\CancelationRequest;
use App\Models\GeneralSettings;
use App\Models\RefundLog;
use App\Traits\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CancelationRequestController extends Controller
{
    use JsonResponse;

    public function index(Request $request): CancelationRequestCollection
    {
        $cancelationRequests = CancelationRequest::all();

        return new CancelationRequestCollection($cancelationRequests);
    }

    public function store(Request $request)
    {
        $booking = Booking::where('booking_id', $request->booking_id)->first();

        if (!$booking) {
            return $this->error('No booking record found!', 404);
        }

        if ($booking->paymentEntry->payment_status !== E_PaymentStatus::SUCCESSFUL) {
            return $this->error('No payment received for this booking!');
        }

        $exist = CancelationRequest::where('booking_id', $booking->id)->first();

        if ($exist) {
            return $this->error("Cancellation request already submitted. We'll notify you once processed by admin.");
        }

        $cancellationFees = GeneralSettings::latest()->first()->cancelation_fees;

        $cancelationRequest = new CancelationRequest();
        $cancelationRequest->booking_id = $booking->id;
        $cancelationRequest->fees = $cancellationFees;
        $cancelationRequest->status = 'pending';
        $cancelationRequest->save();

        //TODO Notify Admin

        return new CancelationRequestResource($cancelationRequest);
    }

    public function show(Request $request, CancelationRequest $cancelationRequest): CancelationRequestResource
    {
        return new CancelationRequestResource($cancelationRequest);
    }

    public function update(CancelationRequestUpdateRequest $request, CancelationRequest $cancelationRequest): CancelationRequestResource
    {
        $cancelationRequest->update($request->validated());

        return new CancelationRequestResource($cancelationRequest);
    }

    public function destroy(Request $request, CancelationRequest $cancelationRequest): Response
    {
        $cancelationRequest->delete();

        return response()->noContent();
    }

    public function proccessCancelationRequest(CancelationRequest $cancelationRequest, Request $request): CancelationRequestResource {
        return DB::transaction(function () use($cancelationRequest, $request) {
            $cancelationRequest->status = $request->status;
            $cancelationRequest->approved_by = auth()->user()->id;
            $cancelationRequest->save();

            if ($request->status === E_Status::APPROVED->value) {
                //initiate refund
                $refundLog = new RefundLog();
                $refundLog->booking_id = $cancelationRequest->booking->id;
                $refundLog->booking_amount = $cancelationRequest->booking->total_amount;
                $refundLog->refund_amount = $cancelationRequest->booking->total_amount - $cancelationRequest->fees;
                $refundLog->fees = $cancelationRequest->fees;
                $refundLog->save();

                $cancelationRequest->booking->is_confirmed = false;
                $cancelationRequest->booking->payment_status = E_PaymentStatus::REFUNDED;
                $cancelationRequest->booking->save();

                $cancelationRequest->booking->room->is_available = true;
                $cancelationRequest->booking->room->save();
            }

            return new CancelationRequestResource($cancelationRequest);
        });
    }
}
