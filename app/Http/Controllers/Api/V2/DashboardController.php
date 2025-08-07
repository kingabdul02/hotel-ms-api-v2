<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
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
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));

        $totalRooms = Room::count();
        $heatmap = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $roomNightsSold = Booking::whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>', $date)
                ->count();
            $occupancyRate = ($totalRooms > 0) ? ($roomNightsSold / $totalRooms) * 100 : 0;

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
                'heatmap' => $heatmap,
                'statistics' => $statistics,
            ],
        ]);
    }

    public function getRevPar(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()));
        $endDate = Carbon::parse($request->input('end_date', now()->endOfMonth()));
        $groupBy = $request->input('group_by', 'day'); // 'day', 'month', 'year'

        $query = Booking::whereBetween('check_in', [$startDate, $endDate]);

        $revparData = $query->select(
            DB::raw("DATE_FORMAT(check_in, '%Y-%m-%d') as period"),
            DB::raw('SUM(total_amount) as total_revenue'),
            DB::raw('COUNT(id) as room_nights_sold')
        )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $totalRooms = Room::count();
        $days = $startDate->diffInDays($endDate) + 1;
        $roomNightsAvailable = $totalRooms * $days;

        $revparData = $revparData->map(function ($item) use ($roomNightsAvailable, $totalRooms) {
            $adr = ($item->room_nights_sold > 0) ? $item->total_revenue / $item->room_nights_sold : 0;
            $occupancyRate = ($roomNightsAvailable > 0) ? ($item->room_nights_sold / $roomNightsAvailable) * 100 : 0;
            $revpar = ($roomNightsAvailable > 0) ? $item->total_revenue / $roomNightsAvailable : 0;

            return [
                'period' => $item->period,
                'revpar' => round($revpar, 2),
                'adr' => round($adr, 2),
                'occupancy_rate' => round($occupancyRate, 2),
                'total_revenue' => $item->total_revenue,
                'room_nights_available' => $roomNightsAvailable,
                'room_nights_sold' => $item->room_nights_sold,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'revpar_data' => $revparData,
            ],
        ]);
    }
}
