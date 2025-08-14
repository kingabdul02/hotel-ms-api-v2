<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\E_PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\BookingCharge;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function modify(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $oldDetails = $booking->toArray();

        $validator = Validator::make($request->all(), [
            'check_in_date' => 'sometimes|date',
            'check_out_date' => 'sometimes|date|after:check_in_date',
            'room_id' => 'sometimes|exists:rooms,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $booking->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Booking modified successfully',
            'data' => [
                'booking_id' => $booking->id,
                'old_details' => $oldDetails,
                'new_details' => $booking,
            ],
        ]);
    }

    public function addCharges(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'charges' => 'required|array',
            'charges.*.description' => 'required|string',
            'charges.*.amount' => 'required|numeric',
            'charges.*.quantity' => 'nullable|integer|min:1',
            'charges.*.category' => 'required|string',
            'charges.*.tax_rate' => 'nullable|numeric',
            'payment_status' => 'required|in:paid,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $chargesAdded = [];
        $totalAmount = 0;
        $totalTax = 0;

        foreach ($request->charges as $chargeData) {
            $amount = (float) ($chargeData['amount'] ?? 0);
            $quantity = isset($chargeData['quantity']) ? max(1, (int) $chargeData['quantity']) : 1;
            $taxRate = (float) ($chargeData['tax_rate'] ?? 0);

            $charge = $booking->charges()->create([
                'description' => $chargeData['description'],
                'amount' => $amount,
                'quantity' => $quantity,
                'category' => $chargeData['category'],
                'tax_rate' => $taxRate,
            ]);

            $chargesAdded[] = $charge->id;
            $totalAmount += $amount * $quantity;
            $totalTax += ($amount * $taxRate * $quantity) / 100;
        }

        $booking->total_amount += $totalAmount + $totalTax;

        // Set paid_amount based on payment_status from payload
        if ($request->payment_status === 'paid') {
            // Only mark as paid if all balance is clear
            if ($booking->paid_amount + $totalAmount + $totalTax >= $booking->total_amount) {
                $booking->paid_amount = $booking->total_amount;
            } else {
                $booking->paid_amount += $totalAmount + $totalTax;
            }
        }
        // If payment_status is pending and no amount is paid, keep paid_amount unchanged

        $booking->balance = $booking->total_amount - $booking->paid_amount;

        // Set payment status
        if ($booking->paid_amount == 0) {
            $booking->payment_status = E_PaymentStatus::PENDING;
        } elseif ($booking->balance <= 0) {
            $booking->payment_status = E_PaymentStatus::PAID;
        } else {
            $booking->payment_status = E_PaymentStatus::PARTIALLY_PAID;
        }

        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Charges added successfully',
            'data' => [
                'charges_added' => count($chargesAdded),
                'total_amount' => $totalAmount,
                'total_tax' => $totalTax,
                'updated_bill_total' => $booking->total_amount,
                'charge_ids' => $chargesAdded,
                'payment_status' => $booking->payment_status,
            ],
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'required|string',
            'refund_amount' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $booking->status = 'cancelled';
        $booking->cancellation_reason = $request->cancellation_reason;
        $booking->save();

        // Logic for refund can be added here

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully',
            'data' => [
                'booking_id' => $booking->id,
                'status' => 'cancelled',
                'cancelled_at' => $booking->updated_at,
            ],
        ]);
    }
}
