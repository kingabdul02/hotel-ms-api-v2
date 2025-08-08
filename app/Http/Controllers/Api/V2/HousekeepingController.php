<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\HousekeeperAssignment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Traits\JsonResponse;

class HousekeepingController extends Controller
{
    use JsonResponse;
    /**
     * Get room statuses with filtering and summary.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoomStatuses(Request $request)
    {
        $query = Room::with('housekeeper:id,name');

        // Filtering
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('housekeeper_id')) {
            $query->where('housekeeper_id', $request->housekeeper_id);
        }
        if ($request->has('date')) {
            $query->whereDate('last_cleaned_at', $request->date);
        }

        $rooms = $query->get();

        $summary = Room::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return $this->success([
            'rooms' => $rooms,
            'summary' => $summary,
        ]);
    }

    /**
     * Update the status of a specific room.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRoomStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:clean,dirty,In-Progress,Out-Of-Service,Maintenance',
            'housekeeper_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $room = Room::findOrFail($id);
        $oldStatus = $room->status;

        $room->status = $request->status;
        $room->housekeeper_id = $request->housekeeper_id;
        $room->notes = $request->notes;

        if ($request->status === 'clean') {
            $room->last_cleaned_at = now();
            // free the housekeeper
            $room->housekeeper_id = null;
            // update the housekeeper assignment
            HousekeeperAssignment::where('room_id', $room->id)
                ->update([
                    'status' => 'completed',
                    // 'completion_time' => now(),
                ]);
        }

        $room->save();

        return $this->success([
            'room_id' => $room->id,
            'old_status' => $oldStatus,
            'new_status' => $room->status,
            'updated_at' => $room->updated_at,
        ]);
    }

    /**
     * Get housekeeper assignments with filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignments(Request $request)
    {
        $query = HousekeeperAssignment::with([
            'housekeeper:id',
            'room'
        ]);

        // Filtering
        if ($request->has('housekeeper_id')) {
            $query->where('housekeeper_id', $request->housekeeper_id);
        }
        if ($request->has('date')) {
            $query->whereDate('assignment_date', $request->date);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->get();

        // Group rooms by housekeeper
        $result = [];
        foreach ($assignments as $assignment) {
            $hkId = $assignment->housekeeper_id;
            if (!isset($result[$hkId])) {
                $result[$hkId] = [
                    'housekeeperId' => $hkId,
                    'rooms' => [],
                ];
            }
            if ($assignment->room) {
                $result[$hkId]['rooms'][] = [
                    'id' => $assignment->room->id,
                    'name' => $assignment->room->name,
                    'roomType' => $assignment->room->roomType->name,
                    'status' => $assignment->room->status,
                    'occupied' => (bool) $assignment->room->is_available,
                    'checkOutTime' => $assignment->room->check_out ? Carbon::parse($assignment->room->check_out)->toIso8601String() : null,
                    'priority' => $assignment->room->priority ?? 'high',
                ];
            }
        }

        return $this->success(array_values($result), 200);
    }

    /**
     * Create or update housekeeper assignments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrUpdateAssignments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array',
            'assignments.*.housekeeper_id' => 'required|exists:users,id',
            'assignments.*.room_ids' => 'required|array',
            'assignments.*.room_ids.*' => 'exists:rooms,id',
            'assignments.*.shift' => 'nullable|string',
            'assignments.*.assignment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $createdCount = 0;
        foreach ($request->assignments as $assignmentData) {
            foreach ($assignmentData['room_ids'] as $roomId) {
                HousekeeperAssignment::updateOrCreate(
                    [
                        'housekeeper_id' => $assignmentData['housekeeper_id'],
                        'room_id' => $roomId,
                        'assignment_date' => $assignmentData['assignment_date'],
                    ],
                    [
                        'shift' => $assignmentData['shift'] ?? null,
                        'status' => 'pending',
                    ]
                );
                $createdCount++;
            }
        }

        return $this->success('Assignments created/updated successfully.', 200);
    }

    public function getAvailableHousekeepers(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $housekeepers = User::role('Admin')->whereDoesntHave('housekeeperAssignments', function ($query) use ($date) {
            $query->where('assignment_date', $date)
                ->whereIn('status', ['pending', 'in_progress']);
        })->paginate(10, ['id', 'name', 'email', 'phone']);

        return $this->success($housekeepers);
    }

    public function getStats(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $yesterday = Carbon::parse($date)->copy()->subDay()->toDateString();

        // Totals and status breakdown
        $totalRooms = Room::count();
        $statusCounts = Room::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $cleanCount = (int) ($statusCounts['clean'] ?? 0);
        $dirtyCount = (int) ($statusCounts['dirty'] ?? 0);
        // support both "In-Progress" and "in_progress" just in case
        $inProgressCount = (int) (($statusCounts['In-Progress'] ?? 0) + ($statusCounts['in_progress'] ?? 0));

        $maintenanceCount = (int) ($statusCounts['Maintenance'] ?? 0);

        $pct = function (int $count) use ($totalRooms): int {
            return $totalRooms > 0 ? (int) round(($count / $totalRooms) * 100) : 0;
        };

        // Housekeepers
        $activeHousekeepers = HousekeeperAssignment::whereDate('assignment_date', $date)
            ->whereIn('status', ['pending', 'in_progress'])
            ->distinct('housekeeper_id')
            ->count('housekeeper_id');

        // Using existing convention in this codebase where housekeepers are queried via role('Admin')
        $totalHousekeepers = User::role('Admin')->count();

        // Efficiency: cleaned today vs assignments today (fallback to room status mix if no assignments)
        $assignmentsToday = HousekeeperAssignment::whereDate('assignment_date', $date)->count();
        $cleanedToday = Room::whereDate('last_cleaned_at', $date)->count();
        if ($assignmentsToday === 0) {
            $assignmentsToday = max(1, $cleanCount + $dirtyCount + $inProgressCount); // avoid div/0
        }
        $effToday = (int) round(($cleanedToday / $assignmentsToday) * 100);

        $assignmentsYday = HousekeeperAssignment::whereDate('assignment_date', $yesterday)->count();
        $cleanedYday = Room::whereDate('last_cleaned_at', $yesterday)->count();
        if ($assignmentsYday === 0) {
            // Approximate using overall totals when no explicit assignments yesterday
            $assignmentsYday = max(1, $totalRooms);
        }
        $effYday = (int) round(($cleanedYday / $assignmentsYday) * 100);
        $delta = round($effToday - $effYday, 1);

        return $this->success([
            'totalRooms' => $totalRooms,
            'cleanRooms' => ['count' => $cleanCount, 'percentage' => $pct($cleanCount)],
            'dirtyRooms' => ['count' => $dirtyCount, 'percentage' => $pct($dirtyCount)],
            'inProgressRooms' => ['count' => $inProgressCount, 'percentage' => $pct($inProgressCount)],
            'maintenanceRooms' => ['count' => $maintenanceCount, 'percentage' => $pct($maintenanceCount)],
            'activeHousekeepers' => ['active' => $activeHousekeepers, 'total' => $totalHousekeepers],
            'efficiencyRate' => ['percentage' => $effToday, 'deltaVsYesterday' => $delta],
            'asOf' => $date,
        ]);
    }
}
