<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorporateBooking;
use App\Models\CorporatePOSCharge;

class CorporatePOSChargeController extends Controller
{
    public function store(Request $request, $corporateBookingId)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:outlet_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'items.*.outlet_id' => 'required|exists:outlets,id',
            'items.*.modifications' => 'nullable|array',
            'items.*.server_id' => 'nullable|exists:users,id',
            'items.*.table_number' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'items.*.payment_status' => 'required|in:paid,pending',
        ]);

        $corporateBooking = CorporateBooking::findOrFail($corporateBookingId);
        $chargesAdded = [];

        foreach ($request->items as $itemData) {
            $charge = $corporateBooking->posCharges()->create([
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'outlet_id' => $itemData['outlet_id'],
                'modifications' => $itemData['modifications'] ?? null,
                'server_id' => $itemData['server_id'] ?? null,
                'table_number' => $itemData['table_number'] ?? null,
                'notes' => $itemData['notes'] ?? null,
                'payment_status' => $itemData['payment_status'],
            ]);
            $chargesAdded[] = $charge->id;
        }

        return response()->json([
            'success' => true,
            'charges_added' => $chargesAdded,
        ]);
    }

    public function index($corporateBookingId)
    {
        $corporateBooking = CorporateBooking::with('posCharges')->findOrFail($corporateBookingId);
        return response()->json([
            'success' => true,
            'pos_charges' => $corporateBooking->posCharges,
        ]);
    }
}
