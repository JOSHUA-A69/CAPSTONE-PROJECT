<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AvailabilityController extends Controller
{
    protected AvailabilityService $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Check availability for a specific slot
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'priest_id' => 'nullable|integer',
            'venue_id' => 'nullable|integer',
            'exclude_reservation_id' => 'nullable|integer',
        ]);

        try {
            $scheduleDate = Carbon::parse($request->date . ' ' . $request->time);
            $priestId = $request->priest_id ? (int) $request->priest_id : null;
            $venueId = $request->venue_id ? (int) $request->venue_id : null;
            $excludeId = $request->exclude_reservation_id ? (int) $request->exclude_reservation_id : null;

            $result = $this->availabilityService->checkFullAvailability(
                $priestId,
                $venueId,
                $scheduleDate,
                $excludeId
            );

            return response()->json([
                'success' => true,
                'available' => $result['available'],
                'priest_available' => $result['priest_available'],
                'venue_available' => $result['venue_available'],
                'messages' => $result['messages'],
                'suggestions' => $result['suggestions'],
            ]);
        } catch (\Exception $e) {
            Log::error('Availability check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability',
            ], 500);
        }
    }

    /**
     * Get available priests for a specific date/time
     */
    public function getAvailablePriests(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'exclude_reservation_id' => 'nullable|integer',
        ]);

        try {
            $scheduleDate = Carbon::parse($request->date . ' ' . $request->time);
            $excludeId = $request->exclude_reservation_id ? (int) $request->exclude_reservation_id : null;

            $availablePriests = $this->availabilityService->getAvailablePriests($scheduleDate, $excludeId);

            // Get all priests to mark availability
            $allPriests = User::where('role', 'priest')
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get()
                ->map(function ($priest) use ($availablePriests) {
                    return [
                        'id' => $priest->id,
                        'name' => $priest->full_name,
                        'available' => $availablePriests->contains('id', $priest->id),
                    ];
                });

            return response()->json([
                'success' => true,
                'priests' => $allPriests,
                'available_count' => $availablePriests->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get available priests failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available priests',
            ], 500);
        }
    }

    /**
     * Get available venues for a specific date/time
     */
    public function getAvailableVenues(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'exclude_reservation_id' => 'nullable|integer',
        ]);

        try {
            $scheduleDate = Carbon::parse($request->date . ' ' . $request->time);
            $excludeId = $request->exclude_reservation_id ? (int) $request->exclude_reservation_id : null;

            $availableVenues = $this->availabilityService->getAvailableVenues($scheduleDate, $excludeId);

            // Get all venues to mark availability
            $allVenues = Venue::orderBy('name')
                ->get()
                ->map(function ($venue) use ($availableVenues) {
                    return [
                        'id' => $venue->venue_id,
                        'name' => $venue->name,
                        'available' => $availableVenues->contains('venue_id', $venue->venue_id),
                    ];
                });

            return response()->json([
                'success' => true,
                'venues' => $allVenues,
                'available_count' => $availableVenues->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Get available venues failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available venues',
            ], 500);
        }
    }

    /**
     * Get available time slots for a specific date
     */
    public function getAvailableTimes(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'priest_id' => 'nullable|integer',
            'venue_id' => 'nullable|integer',
            'exclude_reservation_id' => 'nullable|integer',
        ]);

        try {
            $date = $request->date;
            $priestId = $request->priest_id ? (int) $request->priest_id : null;
            $venueId = $request->venue_id ? (int) $request->venue_id : null;
            $excludeId = $request->exclude_reservation_id ? (int) $request->exclude_reservation_id : null;

            $priestTimes = $priestId 
                ? $this->availabilityService->getAvailableTimesForPriest($priestId, $date, $excludeId)
                : null;

            $venueTimes = $venueId 
                ? $this->availabilityService->getAvailableTimesForVenue($venueId, $date, $excludeId)
                : null;

            // If both priest and venue selected, find intersection
            if ($priestTimes !== null && $venueTimes !== null) {
                $availableTimes = array_intersect($priestTimes, $venueTimes);
            } else {
                $availableTimes = $priestTimes ?? $venueTimes ?? [];
            }

            // Format times for display
            $formattedTimes = array_map(function ($time) {
                return [
                    'value' => $time,
                    'label' => Carbon::parse($time)->format('h:i A'),
                ];
            }, array_values($availableTimes));

            return response()->json([
                'success' => true,
                'times' => $formattedTimes,
                'priest_times' => $priestTimes,
                'venue_times' => $venueTimes,
            ]);
        } catch (\Exception $e) {
            Log::error('Get available times failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available times',
            ], 500);
        }
    }

    /**
     * Get booking summary for a specific date
     */
    public function getDateSummary(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'exclude_reservation_id' => 'nullable|integer',
        ]);

        try {
            $excludeId = $request->exclude_reservation_id ? (int) $request->exclude_reservation_id : null;
            $summary = $this->availabilityService->getDateBookingSummary($request->date, $excludeId);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('Get date summary failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get date summary',
            ], 500);
        }
    }
}
