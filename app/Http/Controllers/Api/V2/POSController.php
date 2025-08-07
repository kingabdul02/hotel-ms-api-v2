<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\Item;
use App\Models\Outlet;
use App\Models\POSCharge;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    public function getOutlets(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => ['outlets' => Outlet::all()],
        ]);
    }

    public function getItems(Request $request)
    {
        $items = Item::where('category_id', $request->category)
            ->when($request->outlet_id, function ($query, $outletId) {
                // Assuming items can be filtered by outlet, this needs a relationship
                // For now, it's a simple filter
                return $query;
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => ['items' => $items],
        ]);
    }

    public function postCharges(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'outlet_id' => 'required|exists:outlets,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $totalAmount = 0;
        $itemsCount = 0;
        $chargeIds = [];

        foreach ($request->items as $itemData) {
            $charge = POSCharge::create(array_merge($itemData, [
                'booking_id' => $request->booking_id,
                'outlet_id' => $request->outlet_id,
            ]));
            $totalAmount += $itemData['quantity'] * $itemData['unit_price'];
            $itemsCount += $itemData['quantity'];
            $chargeIds[] = $charge->id;
        }

        $booking = Booking::find($request->booking_id);
        $booking->total_amount += $totalAmount;
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Charges posted to room successfully',
            'data' => [
                'transaction_ids' => $chargeIds,
                'booking_id' => $booking->id,
                'total_amount' => $totalAmount,
                'items_count' => $itemsCount,
            ],
        ]);
    }

    public function getBill(Request $request, $bookingId)
    {
        $booking = Booking::with(['charges', 'posCharges.item', 'room'])->findOrFail($bookingId);

        $roomCharges = $booking->charges->map(function ($charge) {
            return [
                'description' => $charge->description,
                'amount' => $charge->amount,
            ];
        });

        $posCharges = $booking->posCharges->map(function ($charge) {
            return [
                'date' => $charge->created_at->toDateString(),
                'outlet' => $charge->outlet->name,
                'description' => $charge->item->name,
                'amount' => $charge->quantity * $charge->unit_price,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'booking_id' => $booking->id,
                'guest_name' => $booking->user->name,
                'room_number' => $booking->room->name,
                'stay_dates' => [
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                ],
                'charges' => [
                    'room_charges' => $roomCharges,
                    'pos_charges' => $posCharges,
                ],
                'totals' => [
                    'subtotal' => $booking->total_amount,
                    'tax' => 0, // Simplified
                    'total' => $booking->total_amount,
                ],
                'payment_status' => $booking->payment_status,
            ],
        ]);
    }
}
