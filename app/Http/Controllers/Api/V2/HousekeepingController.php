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

class HousekeepingController extends Controller
{
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

        return response()->json([
            'success' => true,
            'data' => [
                'rooms' => $rooms,
                'summary' => $summary,
            ],
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
            'status' => 'required|string|in:clean,dirty,out_of_order,maintenance',
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
        }

        $room->save();

        return response()->json([
            'success' => true,
            'message' => 'Room status updated successfully.',
            'data' => [
                'room_id' => $room->id,
                'old_status' => $oldStatus,
                'new_status' => $room->status,
                'updated_at' => $room->updated_at,
            ],
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
        $query = HousekeeperAssignment::with('housekeeper:id,name', 'room:id,name,status');

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

        return response()->json([
            'success' => true,
            'data' => [
                'assignments' => $assignments,
            ],
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Assignments updated successfully.',
            'data' => [
                'assignments_created_or_updated' => $createdCount,
            ],
        ], 201);
    }

    public function getAvailableHousekeepers(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $housekeepers = User::role('Housekeeper')->whereDoesntHave('housekeeperAssignments', function ($query) use ($date) {
            $query->where('assignment_date', $date)
                ->whereIn('status', ['pending', 'in_progress']);
        })->get();

        return response()->json($housekeepers);
    }
}
