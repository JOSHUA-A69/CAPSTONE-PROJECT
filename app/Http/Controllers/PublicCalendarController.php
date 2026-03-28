<?php

namespace App\Http\Controllers;

use App\Models\LiturgicalSchedule;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicCalendarController extends Controller
{
    /**
     * Display the public calendar view
     */
    public function index(Request $request)
    {
        // Get all public liturgical schedules
        $liturgicalSchedules = LiturgicalSchedule::with(['priest:id,first_name,middle_name,last_name,email', 'venue:venue_id,name'])
            ->public()
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        // Get admin-approved reservations to display publicly (only after admin confirmation)
        $reservations = Reservation::with([
                'service:service_id,service_name,service_category',
                'venue:venue_id,name',
                'priests:id,first_name,middle_name,last_name,email',
                'officiant:id,first_name,middle_name,last_name,email',
            'organizations:org_id,org_name',
            'organization:org_id,org_name'
            ])
            ->whereIn('status', ['admin_approved', 'approved'])
            ->whereDate('schedule_date', '>=', now()->toDateString())
            ->orderBy('schedule_date')
            ->get();

        Log::info('Public Calendar - Reservations Query', [
            'today' => now()->toDateString(),
            'reservation_count' => $reservations->count(),
            'reservation_ids' => $reservations->pluck('reservation_id')->toArray(),
            'reservation_statuses' => $reservations->pluck('status')->toArray(),
        ]);

        // Transform reservations to match schedule format for the calendar
        // Public view should only show slot occupancy for reservations.
        $reservationSchedules = $reservations->map(function ($reservation) {
            return [
                'schedule_id' => 'reservation_' . $reservation->reservation_id,
                'title' => 'Occupied',
                'event_type' => 'other',
                'mass_subtype' => null,
                'schedule_date' => $reservation->schedule_date->format('Y-m-d'),
                'start_time' => $reservation->schedule_date->format('H:i'),
                'end_time' => $reservation->end_time ? $reservation->end_time->format('H:i') : null,
                'location' => null,
                'venue' => null,
                'priest' => null,
                'main_celebrant' => null,
                'all_priests' => [],
                'organizations' => [],
                'external_priest_name' => null,
                'external_priest_contact' => null,
                'is_public' => true,
                'is_private_reservation' => true,
                'description' => null,
            ];
        });

        // Merge liturgical schedules and reservations
        $schedules = $liturgicalSchedules->concat($reservationSchedules)
            ->sortBy('schedule_date')
            ->values();

        return view('calendar.public', compact('schedules'));
    }

    /**
     * Get public schedules for a specific date (AJAX)
     */
    public function getSchedules(Request $request)
    {
        $date = $request->get('date');

        // Get liturgical schedules for the date
        $liturgicalSchedules = LiturgicalSchedule::with(['priest', 'venue'])
            ->public()
            ->where('schedule_date', $date)
            ->orderBy('start_time')
            ->get();

        // Get admin-approved reservations for the date
        $reservations = Reservation::with([
                'service:service_id,service_name,service_category',
                'venue:venue_id,name',
                'priests:id,first_name,middle_name,last_name,email',
                'officiant:id,first_name,middle_name,last_name,email',
            'organizations:org_id,org_name',
            'organization:org_id,org_name'
            ])
            ->whereIn('status', ['admin_approved', 'approved'])
            ->whereDate('schedule_date', $date)
            ->get();

        // Transform reservations to match schedule format
        // Public view should only show slot occupancy for reservations.
        $reservationSchedules = $reservations->map(function ($reservation) {
            return [
                'schedule_id' => 'reservation_' . $reservation->reservation_id,
                'title' => 'Occupied',
                'event_type' => 'other',
                'schedule_date' => $reservation->schedule_date->format('Y-m-d'),
                'start_time' => $reservation->schedule_date->format('H:i'),
                'end_time' => $reservation->end_time ? $reservation->end_time->format('H:i') : null,
                'location' => null,
                'priest' => null,
                'main_celebrant' => null,
                'all_priests' => [],
                'organizations' => [],
                'external_priest_name' => null,
                'is_private_reservation' => true,
                'description' => null,
            ];
        });

        $schedules = $liturgicalSchedules->concat($reservationSchedules)->sortBy('start_time')->values();

        return response()->json($schedules);
    }
}
