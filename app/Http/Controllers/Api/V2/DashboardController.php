<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getAvailability(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfDay()));
        $endDate = Carbon::parse($request->input('end_date', now()->addMonth()));
        $roomTypeId = $request->input('room_type_id');

        $totalRooms = Room::when($roomTypeId, function ($query, $roomTypeId) {
            return $query->where('room_type_id', $roomTypeId);
        })->count();

        $availability = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $bookedRooms = Booking::whereDate('check_in_date', '<=', $date)
                ->whereDate('check_out_date', '>', $date)
                ->when($roomTypeId, function ($query, $roomTypeId) {
                    return $query->whereHas('room', function ($q) use ($roomTypeId) {
                        $q->where('room_type_id', $roomTypeId);
                    });
                })
                ->count();

            $availableRooms = $totalRooms - $bookedRooms;

            $availability[] = [
                'date' => $date->toDateString(),
                'available_rooms' => $availableRooms,
                'total_rooms' => $totalRooms,
                // 'average_rate' => Room::avg('price'), // Simplified
            ];
        }

        return response()->json([
            'success' => true,
            'data' => ['availability' => $availability],
        ]);
    }

    public function getOccupancyHeatmap(Request $request)
    {
        $period = $request->input('period', 'daily'); // daily, weekly, monthly, custom

        switch ($period) {
            case 'weekly':
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $trendDays = 7;
                break;
            case 'monthly':
                $startDate = now()->subDays(29)->startOfDay();
                $endDate = now()->endOfDay();
                $trendDays = 30;
                break;
            case 'custom':
                $request->validate([
                    'startDate' => 'required|date',
                    'endDate' => 'required|date|after_or_equal:startDate',
                ]);
                $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
                $endDate = Carbon::parse($request->input('endDate'))->endOfDay();
                $trendDays = $startDate->diffInDays($endDate) + 1;
                break;
            case 'daily':
            default:
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                $trendDays = 1;
                break;
        }

        $totalRooms = Room::count();
        $heatmap = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $roomNightsSold = Booking::whereDate('check_in_date', '<=', $date)
                ->whereDate('check_out_date', '>', $date)
                ->distinct('room_id')
                ->count('room_id');

            $occupancyRate = ($totalRooms > 0)
                ? ($roomNightsSold / $totalRooms) * 100
                : 0;

            $heatmap[] = [
                'date' => $date->toDateString(),
                'occupancy_rate' => round($occupancyRate, 2),
                'room_nights_sold' => $roomNightsSold,
                'available_room_nights' => $totalRooms,
            ];
        }

        $statistics = [
            'average_occupancy' => round(collect($heatmap)->avg('occupancy_rate'), 2),
            'peak_occupancy' => round(collect($heatmap)->max('occupancy_rate'), 2),
            'lowest_occupancy' => round(collect($heatmap)->min('occupancy_rate'), 2),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                ],
                'heatmap' => $heatmap,
                'statistics' => $statistics,
            ],
        ]);
    }

    public function getRevenueStats(Request $request)
    {
        $period = $request->input('period', 'today'); // today, 7d, 30d, custom

        switch ($period) {
            case '7d':
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $trendDays = 7;
                break;
            case '30d':
                $startDate = now()->subDays(29)->startOfDay();
                $endDate = now()->endOfDay();
                $trendDays = 30;
                break;
            case 'custom':
                $request->validate([
                    'start_date' => 'required|date',
                    'end_date' => 'required|date|after_or_equal:start_date',
                ]);
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                $trendDays = $startDate->diffInDays($endDate) + 1;
                break;
            case 'today':
            default:
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                $trendDays = 1;
                break;
        }

        // Bookings in selected period
        $bookings = Booking::where('payment_status', 'paid')
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->get();

        $totalRevenue = $bookings->sum('total_amount');

        // Comparison period
        $comparisonStart = $startDate->copy()->subDays($trendDays);
        $comparisonEnd = $startDate->copy()->subDay()->endOfDay();

        $comparisonBookings = Booking::where('payment_status', 'paid')
            ->whereBetween('check_in_date', [$comparisonStart, $comparisonEnd])
            ->get();

        $comparisonRevenue = $comparisonBookings->sum('total_amount');
        $revenueChange = $comparisonRevenue > 0
            ? round((($totalRevenue - $comparisonRevenue) / $comparisonRevenue) * 100, 2)
            : null;

        // Trend logic adjustment
        if (in_array($period, ['today', '7d'])) {
            $trendStart = now()->subDays(6)->startOfDay();
            $trendEnd = now()->endOfDay();
        } else {
            $trendStart = $startDate->copy();
            $trendEnd = $endDate->copy();
        }

        $trend = [];
        for ($d = $trendStart->copy(); $d->lte($trendEnd); $d->addDay()) {
            $dailyRevenue = Booking::where('payment_status', 'paid')
                ->whereDate('check_in_date', $d->toDateString())
                ->sum('total_amount');

            $trend[] = [
                'date' => $d->toDateString(),
                'revenue' => round($dailyRevenue, 2),
            ];
        }

        // Revenue by Room Type with availability & occupancy
        $roomTypes = \App\Models\RoomType::pluck('name', 'id');
        $roomTypeRevenue = [];
        $totalRevenueForBreakdown = $totalRevenue > 0 ? $totalRevenue : 1;

        foreach ($roomTypes as $typeId => $typeName) {
            $roomIds = Room::where('room_type_id', $typeId)->pluck('id');
            $availableRooms = $roomIds->count();

            $typeBookings = $bookings->whereIn('room_id', $roomIds);
            $typeRevenue = $typeBookings->sum('total_amount');

            $bookedRoomCount = $typeBookings->pluck('room_id')->unique()->count();

            $occupancyRate = $availableRooms > 0
                ? round(($bookedRoomCount / $availableRooms) * 100, 2)
                : 0;

            $roomTypeRevenue[] = [
                'roomTypeId' => $typeId,
                'roomTypeName' => $typeName,
                'availableRooms' => $availableRooms,
                'revenue' => round($typeRevenue, 2),
                'percentage' => round(($typeRevenue / $totalRevenueForBreakdown) * 100, 2),
                'occupancyRate' => $occupancyRate
            ];
        }

        usort($roomTypeRevenue, fn($a, $b) => $b['revenue'] <=> $a['revenue']);

        // 🔹 Hotel-level stats
        $totalRooms = Room::count();
        $currentlyOccupied = Booking::where('payment_status', 'paid')
            ->whereDate('check_in_date', '<=', now())
            ->whereDate('check_out_date', '>=', now())
            ->distinct('room_id')
            ->count('room_id');

        $currentOccupancyRate = $totalRooms > 0
            ? round(($currentlyOccupied / $totalRooms) * 100, 2)
            : 0;

        // 🔹 This month stats
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $totalRevenueThisMonth = Booking::where('payment_status', 'paid')
            ->whereBetween('check_in_date', [$monthStart, $monthEnd])
            ->sum('total_amount');

        $prevMonthStart = now()->subMonth()->startOfMonth();
        $prevMonthEnd = now()->subMonth()->endOfMonth();

        $totalRevenuePrevMonth = Booking::where('payment_status', 'paid')
            ->whereBetween('check_in_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('total_amount');

        $totalRevenueThisMonthChange = $totalRevenuePrevMonth > 0
            ? round((($totalRevenueThisMonth - $totalRevenuePrevMonth) / $totalRevenuePrevMonth) * 100, 2)
            : null;

        // 🔹 Extra stats for cards
        $activeBookings = Booking::where('payment_status', 'paid')
            ->whereDate('check_in_date', '<=', now())
            ->whereDate('check_out_date', '>=', now())
            ->count();

        $todaysCheckIns = Booking::where('payment_status', 'paid')
            ->whereDate('check_in_date', now()->toDateString())
            ->count();

        $averageDailyRate = $bookings->count() > 0
            ? round($totalRevenue / $bookings->count(), 2)
            : 0;

        // 🔹 Final Response
        return response()->json([
            'success' => true,
            'data' => [
                'totalRevenue' => round($totalRevenue, 2),
                'revenueChange' => $revenueChange,
                'trend' => $trend,
                'breakdown' => $roomTypeRevenue,

                // Extra stats
                'averageDailyRate' => $averageDailyRate,
                'activeBookings' => $activeBookings,
                'todaysCheckIns' => $todaysCheckIns,

                // New stats
                'totalRevenueThisMonth' => round($totalRevenueThisMonth, 2),
                'totalRevenueThisMonthChange' => $totalRevenueThisMonthChange,
                'currentOccupancyRate' => [
                    'rate' => $currentOccupancyRate,
                    'occupied' => $currentlyOccupied,
                    'totalRooms' => $totalRooms
                ],

                // Period info
                'period' => [
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                    'comparisonStartDate' => $comparisonStart->toDateString(),
                    'comparisonEndDate' => $comparisonEnd->toDateString(),
                ],
                'currency' => 'NGN',
                'generatedAt' => now()->toISOString(),
            ],
        ]);
    }

    public function availabilityCalendar(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $startDate = Carbon::parse($request->input('startDate'))->startOfDay();
        $endDate = Carbon::parse($request->input('endDate'))->startOfDay();

        $roomTypes = RoomType::withCount('rooms')
            ->get()
            ->map(fn($rt) => [
                'id' => $rt->id,
                'name' => $rt->name,
                'totalRooms' => $rt->rooms_count,
            ]);

        // Preload rooms grouped by room_type_id
        $roomsByType = Room::select('id', 'room_type_id', 'price')
            ->get()
            ->groupBy('room_type_id');

        // Preload bookings overlapping the range (inclusive)
        $bookings = Booking::select('id', 'room_id', 'check_in_date', 'check_out_date')
            ->whereDate('check_in_date', '<=', $endDate)
            ->whereDate('check_out_date', '>', $startDate) // checkout date is exclusive
            ->get();

        // Index bookings by room_id for quick access
        $bookingsByRoom = $bookings->groupBy('room_id');

        $availability = [];

        foreach ($roomTypes as $rt) {
            $typeId = $rt['id'];
            $rooms = $roomsByType->get($typeId, collect());
            $totalRooms = $rt['totalRooms'];
            $availability[$typeId] = [];

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $occupiedCount = 0;
                $rates = [];

                foreach ($rooms as $room) {
                    $roomBookings = $bookingsByRoom->get($room->id, collect());
                    // A booking occupies the room for nights: [check_in_date, check_out_date)
                    $isOccupied = $roomBookings->first(function ($b) use ($date) {
                        return $b->check_in_date->lte($date) && $b->check_out_date->gt($date);
                    });
                    if ($isOccupied) {
                        $occupiedCount++;
                    }
                    if (!is_null($room->price)) {
                        $rates[] = $room->price;
                    }
                }

                $available = max($totalRooms - $occupiedCount, 0);
                $avgRate = count($rates) ? round(array_sum($rates) / count($rates), 2) : 0;

                $availability[$typeId][$dateStr] = [
                    'available' => $available,
                    'rate' => $avgRate,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Availability loaded',
            'data' => [
                'roomTypes' => $roomTypes,
                'availability' => $availability,
            ],
        ]);
    }
}
