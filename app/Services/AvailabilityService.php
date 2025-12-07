<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Default duration for a Mass/Service in hours
     */
    const DEFAULT_SERVICE_DURATION = 2;

    /**
     * Statuses that count as "booked" (not available)
     */
    const BOOKED_STATUSES = [
        'pending',
        'approved',
        'adviser_approved',
        'admin_approved',
        'confirmed',
        'completed',
    ];

    /**
     * Check if a priest is available for a given time slot
     */
    public function isPriestAvailable(
        int $priestId,
        Carbon $scheduleDate,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): array {
        $startTime = $scheduleDate->copy();
        $endTime = $scheduleDate->copy()->addHours($durationHours);

        $conflictingReservation = $this->findPriestConflict($priestId, $startTime, $endTime, $excludeReservationId);

        if ($conflictingReservation) {
            return [
                'available' => false,
                'conflict' => $conflictingReservation,
                'message' => "Priest is already assigned to '{$conflictingReservation->activity_name}' from " .
                    $conflictingReservation->schedule_date->format('h:i A') . " to " .
                    $conflictingReservation->schedule_date->copy()->addHours($durationHours)->format('h:i A'),
            ];
        }

        return ['available' => true, 'conflict' => null, 'message' => null];
    }

    /**
     * Check if a venue is available for a given time slot
     */
    public function isVenueAvailable(
        int $venueId,
        Carbon $scheduleDate,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): array {
        $startTime = $scheduleDate->copy();
        $endTime = $scheduleDate->copy()->addHours($durationHours);

        $conflictingReservation = $this->findVenueConflict($venueId, $startTime, $endTime, $excludeReservationId);

        if ($conflictingReservation) {
            $venue = Venue::find($venueId);
            return [
                'available' => false,
                'conflict' => $conflictingReservation,
                'message' => "Venue '{$venue->name}' is already booked for '{$conflictingReservation->activity_name}' from " .
                    $conflictingReservation->schedule_date->format('h:i A') . " to " .
                    $conflictingReservation->schedule_date->copy()->addHours($durationHours)->format('h:i A'),
            ];
        }

        return ['available' => true, 'conflict' => null, 'message' => null];
    }

    /**
     * Check full availability (both priest and venue)
     */
    public function checkFullAvailability(
        ?int $priestId,
        ?int $venueId,
        Carbon $scheduleDate,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): array {
        $result = [
            'available' => true,
            'priest_available' => true,
            'venue_available' => true,
            'priest_conflict' => null,
            'venue_conflict' => null,
            'messages' => [],
            'suggestions' => [],
        ];

        // Check priest availability
        if ($priestId) {
            $priestCheck = $this->isPriestAvailable($priestId, $scheduleDate, $excludeReservationId, $durationHours);
            if (!$priestCheck['available']) {
                $result['available'] = false;
                $result['priest_available'] = false;
                $result['priest_conflict'] = $priestCheck['conflict'];
                $result['messages'][] = $priestCheck['message'];

                // Get alternative priests
                $alternativePriests = $this->getAvailablePriests($scheduleDate, $excludeReservationId, $durationHours);
                if ($alternativePriests->isNotEmpty()) {
                    $result['suggestions'][] = [
                        'type' => 'priest',
                        'message' => 'Available priests: ' . $alternativePriests->pluck('full_name')->join(', '),
                        'alternatives' => $alternativePriests,
                    ];
                }

                // Suggest alternative times with same priest
                $alternativeTimes = $this->getAvailableTimesForPriest($priestId, $scheduleDate->toDateString(), $excludeReservationId, $durationHours);
                if (!empty($alternativeTimes)) {
                    $result['suggestions'][] = [
                        'type' => 'time',
                        'message' => 'Available times with this priest: ' . implode(', ', array_map(fn($t) => Carbon::parse($t)->format('h:i A'), $alternativeTimes)),
                        'alternatives' => $alternativeTimes,
                    ];
                }
            }
        }

        // Check venue availability
        if ($venueId) {
            $venueCheck = $this->isVenueAvailable($venueId, $scheduleDate, $excludeReservationId, $durationHours);
            if (!$venueCheck['available']) {
                $result['available'] = false;
                $result['venue_available'] = false;
                $result['venue_conflict'] = $venueCheck['conflict'];
                $result['messages'][] = $venueCheck['message'];

                // Get alternative venues
                $alternativeVenues = $this->getAvailableVenues($scheduleDate, $excludeReservationId, $durationHours);
                if ($alternativeVenues->isNotEmpty()) {
                    $result['suggestions'][] = [
                        'type' => 'venue',
                        'message' => 'Available venues: ' . $alternativeVenues->pluck('name')->join(', '),
                        'alternatives' => $alternativeVenues,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Find conflicting reservation for a priest
     */
    protected function findPriestConflict(
        int $priestId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeReservationId = null
    ): ?Reservation {
        return Reservation::where('officiant_id', $priestId)
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $startTime->toDateString())
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->get()
            ->filter(function ($reservation) use ($startTime, $endTime) {
                return $this->timeSlotsOverlap(
                    $reservation->schedule_date,
                    $reservation->schedule_date->copy()->addHours(self::DEFAULT_SERVICE_DURATION),
                    $startTime,
                    $endTime
                );
            })
            ->first();
    }

    /**
     * Find conflicting reservation for a venue
     */
    protected function findVenueConflict(
        int $venueId,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeReservationId = null
    ): ?Reservation {
        return Reservation::where('venue_id', $venueId)
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $startTime->toDateString())
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->get()
            ->filter(function ($reservation) use ($startTime, $endTime) {
                return $this->timeSlotsOverlap(
                    $reservation->schedule_date,
                    $reservation->schedule_date->copy()->addHours(self::DEFAULT_SERVICE_DURATION),
                    $startTime,
                    $endTime
                );
            })
            ->first();
    }

    /**
     * Check if two time slots overlap
     */
    protected function timeSlotsOverlap(
        Carbon $start1,
        Carbon $end1,
        Carbon $start2,
        Carbon $end2
    ): bool {
        // Two slots overlap if start1 < end2 AND start2 < end1
        return $start1->lt($end2) && $start2->lt($end1);
    }

    /**
     * Get all available priests for a given time slot
     */
    public function getAvailablePriests(
        Carbon $scheduleDate,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): Collection {
        $allPriests = User::where('role', 'priest')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $startTime = $scheduleDate->copy();
        $endTime = $scheduleDate->copy()->addHours($durationHours);

        // Get priest IDs that have conflicts
        $busyPriestIds = Reservation::whereNotNull('officiant_id')
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $scheduleDate->toDateString())
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->get()
            ->filter(function ($reservation) use ($startTime, $endTime) {
                return $this->timeSlotsOverlap(
                    $reservation->schedule_date,
                    $reservation->schedule_date->copy()->addHours(self::DEFAULT_SERVICE_DURATION),
                    $startTime,
                    $endTime
                );
            })
            ->pluck('officiant_id')
            ->unique()
            ->toArray();

        return $allPriests->filter(fn($priest) => !in_array($priest->id, $busyPriestIds));
    }

    /**
     * Get all available venues for a given time slot
     */
    public function getAvailableVenues(
        Carbon $scheduleDate,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): Collection {
        $allVenues = Venue::orderBy('name')->get();

        $startTime = $scheduleDate->copy();
        $endTime = $scheduleDate->copy()->addHours($durationHours);

        // Get venue IDs that have conflicts
        $busyVenueIds = Reservation::whereNotNull('venue_id')
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $scheduleDate->toDateString())
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->get()
            ->filter(function ($reservation) use ($startTime, $endTime) {
                return $this->timeSlotsOverlap(
                    $reservation->schedule_date,
                    $reservation->schedule_date->copy()->addHours(self::DEFAULT_SERVICE_DURATION),
                    $startTime,
                    $endTime
                );
            })
            ->pluck('venue_id')
            ->unique()
            ->toArray();

        return $allVenues->filter(fn($venue) => !in_array($venue->venue_id, $busyVenueIds));
    }

    /**
     * Get available time slots for a priest on a specific date
     */
    public function getAvailableTimesForPriest(
        int $priestId,
        string $date,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): array {
        // Define possible time slots (6 AM to 8 PM in hourly increments)
        $possibleTimes = [];
        for ($hour = 6; $hour <= 20; $hour++) {
            $possibleTimes[] = sprintf('%02d:00:00', $hour);
        }

        // Get booked times for this priest
        $bookedReservations = Reservation::where('officiant_id', $priestId)
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $date)
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->get();

        $availableTimes = [];

        foreach ($possibleTimes as $time) {
            $slotStart = Carbon::parse("$date $time");
            $slotEnd = $slotStart->copy()->addHours($durationHours);

            $hasConflict = $bookedReservations->contains(function ($reservation) use ($slotStart, $slotEnd, $durationHours) {
                return $this->timeSlotsOverlap(
                    $reservation->schedule_date,
                    $reservation->schedule_date->copy()->addHours($durationHours),
                    $slotStart,
                    $slotEnd
                );
            });

            if (!$hasConflict) {
                $availableTimes[] = $time;
            }
        }

        return $availableTimes;
    }

    /**
     * Get available time slots for a venue on a specific date
     */
    public function getAvailableTimesForVenue(
        int $venueId,
        string $date,
        ?int $excludeReservationId = null,
        int $durationHours = self::DEFAULT_SERVICE_DURATION
    ): array {
        // Define possible time slots (6 AM to 8 PM in hourly increments)
        $possibleTimes = [];
        for ($hour = 6; $hour <= 20; $hour++) {
            $possibleTimes[] = sprintf('%02d:00:00', $hour);
        }

        // Get booked times for this venue
        $bookedReservations = Reservation::where('venue_id', $venueId)
            ->whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $date)
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->get();

        $availableTimes = [];

        foreach ($possibleTimes as $time) {
            $slotStart = Carbon::parse("$date $time");
            $slotEnd = $slotStart->copy()->addHours($durationHours);

            $hasConflict = $bookedReservations->contains(function ($reservation) use ($slotStart, $slotEnd, $durationHours) {
                return $this->timeSlotsOverlap(
                    $reservation->schedule_date,
                    $reservation->schedule_date->copy()->addHours($durationHours),
                    $slotStart,
                    $slotEnd
                );
            });

            if (!$hasConflict) {
                $availableTimes[] = $time;
            }
        }

        return $availableTimes;
    }

    /**
     * Get booking summary for a specific date
     */
    public function getDateBookingSummary(string $date, ?int $excludeReservationId = null): array
    {
        $reservations = Reservation::whereIn('status', self::BOOKED_STATUSES)
            ->whereDate('schedule_date', $date)
            ->when($excludeReservationId, fn($q) => $q->where('reservation_id', '!=', $excludeReservationId))
            ->with(['officiant', 'venue'])
            ->orderBy('schedule_date')
            ->get();

        $priestBookings = [];
        $venueBookings = [];

        foreach ($reservations as $reservation) {
            $startTime = $reservation->schedule_date->format('H:i');
            $endTime = $reservation->schedule_date->copy()->addHours(self::DEFAULT_SERVICE_DURATION)->format('H:i');
            $timeSlot = "$startTime - $endTime";

            if ($reservation->officiant_id) {
                $priestBookings[$reservation->officiant_id][] = [
                    'reservation_id' => $reservation->reservation_id,
                    'activity' => $reservation->activity_name,
                    'time_slot' => $timeSlot,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];
            }

            if ($reservation->venue_id) {
                $venueBookings[$reservation->venue_id][] = [
                    'reservation_id' => $reservation->reservation_id,
                    'activity' => $reservation->activity_name,
                    'time_slot' => $timeSlot,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];
            }
        }

        return [
            'priest_bookings' => $priestBookings,
            'venue_bookings' => $venueBookings,
            'total_reservations' => $reservations->count(),
        ];
    }
}
