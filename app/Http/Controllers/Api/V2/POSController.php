<?php

namespace App\Http\Controllers\Api\V2;

use App\Enums\E_PaymentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\Booking;
use App\Http\Requests\POS\GetPOSItemsRequest;
use App\Http\Requests\POS\GetOutletsRequest;
use App\Http\Resources\OutletItemCategoryResource;
use App\Models\Item;
use App\Models\OutletItem;
use App\Models\OutletItemCategory;
use App\Models\Outlet;
use App\Models\POSCharge;
use Illuminate\Support\Facades\Validator;

class POSController extends Controller
{
    public function getOutlets(GetOutletsRequest $request)
    {
        $perPage = (int) ($request->input('per_page', 50));

        $query = Outlet::query()
            ->when($request->filled('name'), function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('name') . '%');
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->input('type'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%$term%")
                        ->orWhere('type', 'like', "%$term%")
                        ->orWhere('status', 'like', "%$term%");
                });
            })
            ->orderBy('name');

        $outlets = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $outlets,
        ]);
    }

    public function getItems(GetPOSItemsRequest $request)
    {
        // Fetch categories for the outlet, optionally filter to a single category
        $categoriesQuery = OutletItemCategory::query()
            ->where('outlet_id', $request->outlet_id)
            ->when($request->filled('category'), function ($q) use ($request) {
                $cat = $request->input('category');
                if (is_numeric($cat)) {
                    $q->where('id', (int) $cat);
                } else {
                    $q->where('name', 'like', $cat);
                }
            })
            ->with(['items' => function ($q) {
                $q->orderBy('name');
            }])
            ->orderBy('name');

        $categories = $categoriesQuery->get();

        // Map to expected structure in docs
        $response = [
            'categories' => $categories->map(function ($cat) {
                return [
                    'name' => $cat->name,
                    'items' => $cat->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'price' => (float) $item->price,
                            'description' => $item->description,
                            'available' => (bool) $item->available,
                            'tax_rate' => (float) $item->tax_rate,
                        ];
                    }),
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function postCharges(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'outlet_id' => 'required|exists:outlets,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:outlet_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'payment_status' => 'required|in:paid,pending',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $totalAmount = 0;
        $itemsCount = 0;
        $chargeIds = [];

        foreach ($request->items as $itemData) {
            // Ensure the item belongs to the outlet
            $outletItem = \App\Models\OutletItem::where('id', $itemData['item_id'])
                ->where('outlet_id', $request->outlet_id)
                ->first();

            if (!$outletItem) {
                return response()->json([
                    'success' => false,
                    'errors' => ['items' => ['One or more items do not belong to the specified outlet']],
                ], 422);
            }

            $charge = POSCharge::create(array_merge($itemData, [
                'booking_id' => $request->booking_id,
                'outlet_id' => $request->outlet_id,
                'payment_status' => $request->payment_status,
            ]));
            $totalAmount += $itemData['quantity'] * $itemData['unit_price'];
            $itemsCount += $itemData['quantity'];
            $chargeIds[] = $charge->id;
        }

        $booking = Booking::find($request->booking_id);

        // Only update paid_amount if payment_status is 'paid' and all balance is clear
        if ($request->payment_status === 'paid') {
            // Calculate new total and paid amounts
            $newTotalAmount = $booking->total_amount + $totalAmount;
            $newPaidAmount = $booking->paid_amount + $totalAmount;

            if ($newPaidAmount >= $newTotalAmount) {
                $booking->paid_amount = $newPaidAmount;
                $booking->payment_status = E_PaymentStatus::PAID;
            } else {
                // Not fully paid, set as partially paid
                $booking->paid_amount = $newPaidAmount;
                $booking->payment_status = E_PaymentStatus::PARTIALLY_PAID;
            }
            $booking->total_amount = $newTotalAmount;
            $booking->balance = max($booking->total_amount - $booking->paid_amount, 0);
        } else {
            // If no amount is paid, keep payment_status as pending
            $booking->total_amount += $totalAmount;
            if ($booking->paid_amount == 0) {
                $booking->payment_status = E_PaymentStatus::PENDING;
            } else {
                $booking->payment_status = E_PaymentStatus::PARTIALLY_PAID;
            }
            $booking->balance = max($booking->total_amount - $booking->paid_amount, 0);
        }

        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Charges posted to room successfully',
            'data' => [
                'transaction_ids' => $chargeIds,
                'total_amount' => $totalAmount,
                'items_count' => $itemsCount,
                'payment_status' => $booking->payment_status,
            ],
        ]);
    }

    public function getBill(Request $request, $bookingId)
    {
        // Eager-load all relations used to avoid N+1
        $booking = Booking::with(['charges', 'posCharges.item', 'posCharges.outlet', 'room', 'user'])
            ->findOrFail($bookingId);

        // Derive nights
        $nights = $booking->no_of_nights;
        if (!$nights && $booking->check_in_date && $booking->check_out_date) {
            $nights = Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date));
        }
        $nights = max(0, (int) $nights);

        // Split booking charges into accommodation vs others if categories exist
        $accommodationCategories = ['accommodation', 'room', 'room_rate', 'lodging'];

        $accommodationChargesLines = collect();
        $otherRoomChargesLines = collect();
        $accommodationSubtotal = 0.0;
        $accommodationTax = 0.0;

        foreach ($booking->charges as $charge) {
            $qty = (int) ($charge->quantity ?: 1);
            $unit = (float) $charge->amount;
            $lineSubtotal = $unit * $qty;
            $taxRate = (float) ($charge->tax_rate ?: 0);
            $lineTax = ($lineSubtotal * $taxRate) / 100;

            $line = [
                'date' => optional($charge->created_at)->toDateString(),
                'description' => $charge->description,
                'quantity' => $qty,
                'unit_price' => $unit,
                'tax_rate' => $taxRate,
                'subtotal' => $lineSubtotal,
                'tax' => $lineTax,
                'total' => $lineSubtotal + $lineTax,
                'category' => $charge->category,
            ];

            if ($charge->category && in_array(strtolower($charge->category), $accommodationCategories, true)) {
                $accommodationChargesLines->push($line);
                $accommodationSubtotal += $lineSubtotal;
                $accommodationTax += $lineTax;
            } else {
                $otherRoomChargesLines->push($line);
            }
        }

        // If there are no explicit accommodation charge lines, compute from room rate * nights
        if ($accommodationChargesLines->isEmpty()) {
            $roomRate = (float) optional($booking->room)->price ?: 0.0;
            $computedSubtotal = $roomRate * $nights;
            $accommodationSubtotal = $computedSubtotal;
            $accommodationTax = 0.0; // No explicit tax info for base rate
        }

        // Map POS charges and compute tax using item's tax_rate if present
        $posChargesLines = $booking->posCharges->map(function ($charge) {
            $qty = (int) $charge->quantity;
            $unit = (float) $charge->unit_price;
            $subtotal = $qty * $unit;
            $taxRate = (float) optional($charge->item)->tax_rate ?: 0.0;
            $tax = ($subtotal * $taxRate) / 100;

            return [
                'date' => optional($charge->created_at)->toDateString(),
                'outlet' => optional($charge->outlet)->name,
                'description' => optional($charge->item)->name,
                'quantity' => $qty,
                'unit_price' => $unit,
                'tax_rate' => $taxRate,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
            ];
        });

        // Totals
        $roomChargesSubtotal = (float) $otherRoomChargesLines->sum('subtotal');
        $roomChargesTax = (float) $otherRoomChargesLines->sum('tax');
        $posSubtotal = (float) $posChargesLines->sum('subtotal');
        $posTax = (float) $posChargesLines->sum('tax');

        $subtotal = $accommodationSubtotal + $roomChargesSubtotal + $posSubtotal;
        $tax = $accommodationTax + $roomChargesTax + $posTax;
        $total = $subtotal + $tax;

        // Payments
        $paid = (float) ($booking->paid_amount ?? 0.0);
        $balance = max($total - $paid, 0);

        return response()->json([
            'success' => true,
            'data' => [
                // 'booking_id' => $booking->id,
                // 'guest_name' => $booking->guest_name ?: optional($booking->user)->name,
                // 'room_number' => optional($booking->room)->name,
                // 'stay_dates' => [
                //     'check_in_date' => $booking->check_in_date,
                //     'check_out_date' => $booking->check_out_date,
                //     'nights' => $nights,
                // ],
                "booking" => [
                    "id" => $booking->id,
                    "guest_name" => $booking->guest_name ?: optional($booking->user)->name,
                    "room_number" => optional($booking->room)->name,
                    "check_in_date" => $booking->check_in_date,
                    "check_out_date" => $booking->check_out_date,
                    "special_requests" => $booking->special_requests,
                    "total_amount" => $booking->total_amount,
                    "paid_amount" => $booking->paid_amount,
                    "balance" => $booking->balance,
                    "payment_status" => $booking->payment_status,
                    "is_confirmed" => $booking->is_confirmed,
                    "is_checked_in" => $booking->is_checked_in,
                    "is_checked_out" => $booking->is_checked_out,
                    "no_of_guests" => $booking->no_of_guests,
                    "no_of_nights" => $booking->no_of_nights,
                    "booking_id" => $booking->booking_id,
                    "guest_name" => $booking->guest_name,
                    "is_online_booking" => $booking->is_online_booking,
                    "status" =>  $booking->status,
                    "cancellation_reason" =>  $booking->cancellation_reason,
                    "created_at" =>  $booking->created_at,
                    "updated_at" =>  $booking->updated_at,
                ],
                // Keep existing key for backward compatibility (typo preserved)
                // 'accomodation_charges' => $accommodationSubtotal + $accommodationTax,
                'charges' => [
                    'accommodation' => [
                        'lines' => $accommodationChargesLines->values(),
                        'subtotal' => $accommodationSubtotal,
                        'tax' => $accommodationTax,
                        'total' => $accommodationSubtotal + $accommodationTax,
                    ],
                    'booking_charges' => $otherRoomChargesLines->values(),
                    'pos_charges' => $posChargesLines->values(),
                ],
                'totals' => [
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $total,
                    'paid' => $paid,
                    'balance' => $balance,
                ],
                'payment_status' => $booking->payment_status,
            ],
        ]);
    }
}
