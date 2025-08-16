<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorporateBooking;
use App\Models\CorporateBookingCharge;

class CorporateBookingChargeController extends Controller
{
    public function store(Request $request, $corporateBookingId)
    {
        $request->validate([
            'charges' => 'required|array',
            'charges.*.description' => 'required|string',
            'charges.*.amount' => 'required|numeric',
            'charges.*.quantity' => 'nullable|integer|min:1',
            'charges.*.category' => 'required|string',
            'charges.*.tax_rate' => 'nullable|numeric',
        ]);

        $corporateBooking = CorporateBooking::findOrFail($corporateBookingId);
        $chargesAdded = [];

        foreach ($request->charges as $chargeData) {
            $charge = $corporateBooking->charges()->create([
                'description' => $chargeData['description'],
                'amount' => $chargeData['amount'],
                'quantity' => $chargeData['quantity'] ?? 1,
                'category' => $chargeData['category'],
                'tax_rate' => $chargeData['tax_rate'] ?? null,
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
        $corporateBooking = CorporateBooking::with('charges')->findOrFail($corporateBookingId);
        return response()->json([
            'success' => true,
            'charges' => $corporateBooking->charges,
        ]);
    }
}
