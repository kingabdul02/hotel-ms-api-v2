<?php

namespace App\Http\Controllers;

use App\Http\Resources\ItemCollection;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class StatisticCotroller extends Controller
{
    public function getBookingStats(Request $request)
    {
        $eightMonthsAgo = Carbon::now()->subMonths(8);
        $twoDaysAgo = Carbon::yesterday()->subDay(1);
        $yesterday = Carbon::yesterday();
        $today = Carbon::today();

        // Search/filter inputs
        $search = $request->input('search');
        $roomTypeId = $request->input('room_type_id');
        $checkInDate = $request->input('check_in_date');
        $checkOutDate = $request->input('check_out_date');

        // Revenue by room type
        $totalRevenueByRoomType = Booking::join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->whereBetween('bookings.check_in_date', [$eightMonthsAgo, Carbon::now()])
            ->where('bookings.payment_status', 'paid')
            ->groupBy(DB::raw('YEAR(bookings.check_in_date)'), DB::raw('MONTH(bookings.check_in_date)'), 'rooms.room_type_id', 'room_types.name', 'room_types.chart_color_code')
            ->selectRaw('room_types.name as room_type, room_types.chart_color_code as color_code, YEAR(bookings.check_in_date) as year, MONTH(bookings.check_in_date) as month, SUM(bookings.total_amount) as total_revenue')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->groupBy(fn($date) => Carbon::createFromDate($date->year, $date->month, 1)->format('F Y'));

        // Check-in / Check-out stats
        $checkInCheckOutStats = Booking::whereBetween('check_in_date', [$eightMonthsAgo, Carbon::now()])
            ->orWhereBetween('check_out_date', [$eightMonthsAgo, Carbon::now()])
            ->selectRaw('
            YEAR(check_in_date) as year,
            MONTH(check_in_date) as month,
            COUNT(CASE WHEN check_in_date IS NOT NULL THEN 1 END) as total_bookings,
            SUM(CASE WHEN is_checked_in = 1 THEN 1 ELSE 0 END) as confirmed_check_ins,
            SUM(CASE WHEN is_checked_out = 1 THEN 1 ELSE 0 END) as confirmed_check_outs
        ')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->groupBy(fn($date) => Carbon::createFromDate($date->year, $date->month, 1)->format('F Y'));

        // Today stats
        $checkInsToday = Booking::whereDate('check_in_date', $today)
            ->where('is_checked_in', true)
            ->count();

        $checkOutsToday = Booking::whereDate('check_out_date', $today)
            ->where('is_checked_out', true)
            ->count();

        $expectedCheckIns = Booking::whereDate('check_in_date', $today)
            ->where('is_checked_in', false)
            ->count();

        $expectedCheckOuts = Booking::whereDate('check_out_date', $today)
            ->where('is_checked_out', false)
            ->count();

        $availableRoomCount = Room::where('is_available', true)->count();

        // Bookings with search and filters
        $recentBookingsQuery = Booking::with('room', 'user')->latest();

        if ($search) {
            $recentBookingsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })->orWhereHas('room', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        if ($roomTypeId) {
            $recentBookingsQuery->whereHas('room', function ($q) use ($roomTypeId) {
                $q->where('room_type_id', $roomTypeId);
            });
        }

        if ($checkInDate) {
            $recentBookingsQuery->whereDate('check_in_date', $checkInDate);
        }

        if ($checkOutDate) {
            $recentBookingsQuery->whereDate('check_out_date', $checkOutDate);
        }

        $recentBookings = $recentBookingsQuery->orderBy('check_in_date', 'desc')->paginate(100);

        return response()->json([
            'totalRevenueByRoomType'   => $totalRevenueByRoomType,
            'checkInCheckOutStats'     => $checkInCheckOutStats,
            'checkInsToday'            => $checkInsToday,
            'checkOutsToday'           => $checkOutsToday,
            'expectedCheckInsToday'    => $expectedCheckIns,
            'expectedCheckOutsToday'   => $expectedCheckOuts,
            'availableRoomCount'       => $availableRoomCount,
            'recentBookings'           => $recentBookings,
        ]);
    }

    public function getInventoryStats(Request $request)
    {
        $totalProducts = Item::all()->count();

        $mostUsedItems = InventoryMovement::select('items.name', DB::raw('SUM(inventory_movements.quantity) as total_quantity_used'))
            ->join('items', 'inventory_movements.item_id', '=', 'items.id')
            ->where('inventory_movements.transaction_type', 'OUT')
            ->where('inventory_movements.transaction_date', '>=', Carbon::now()->subDays(7))
            ->groupBy('items.id', 'items.name')
            ->orderBy('total_quantity_used', 'DESC')
            ->get();

        $lowStockItems = Item::with('inventory')
            ->whereHas('inventory', function (Builder $query) {
                $query->whereColumn('quantity', '<', 'items.reorder_level')
                    ->where('quantity', '>', 0);
            })->get();


        $outOfStockItems = Item::with('inventory', 'category')
            ->whereHas('inventory', function (Builder $query) {
                $query->where('quantity', '=', 0);
            })->get();

        $statistics = Category::with(['items.inventory' => function ($query) {
            $query->where('quantity', '>', 1);
        }])
            ->get()
            ->map(function ($category) {
                $totalQuantity = $category->items->sum(function ($item) {
                    return optional($item->inventory)->quantity ?? 0;
                });

                return [
                    'category_name' => $category->name,
                    'total_quantity' => $totalQuantity,
                    'chart_color' => $category->chart_color,
                ];
            })
            ->filter(function ($category) {
                return $category['total_quantity'] > 0;
            })
            ->values();

        $items = Item::all();

        return response()->json([
            'totalProducts' => $totalProducts,
            'mostUsedItems' => $mostUsedItems,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
            'products' => new ItemCollection($items->load('inventory', 'category')),
            'statistics' => $statistics,
        ]);
    }
}
