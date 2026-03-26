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
        $reservationSchedules = $reservations->map(function ($reservation) {
            // Determine priest names with main celebrant indication
            $priestName = null;
            $mainCelebrant = null;
            $allPriests = [];

            if ($reservation->priests && $reservation->priests->count() > 0) {
                $firstPriestName = null;
                foreach ($reservation->priests as $priest) {
                    $name = $priest->full_name;
                    if ($firstPriestName === null) {
                        $firstPriestName = $name;
                    }

                    $isMainCelebrant = ($priest->pivot->is_main_celebrant ?? false)
                        || ((int) $reservation->officiant_id === (int) $priest->id);

                    if ($isMainCelebrant) {
                        $mainCelebrant = $name;
                        $name .= ' (Main Celebrant)';
                    }
                    $allPriests[] = $name;
                }
                if ($mainCelebrant === null) {
                    $mainCelebrant = $firstPriestName;
                }
                $priestName = implode(', ', $allPriests);
            } elseif ($reservation->officiant) {
                $priestName = $reservation->officiant->full_name;
                $mainCelebrant = $priestName;
            } elseif ($reservation->external_priest_name) {
                $priestName = $reservation->external_priest_name . ' (External)';
                $mainCelebrant = $reservation->external_priest_name;
            }

            // Get organizations
            $organizationNames = [];
            if ($reservation->organizations && $reservation->organizations->count() > 0) {
                $organizationNames = $reservation->organizations->pluck('org_name')->toArray();
            } elseif ($reservation->organization) {
                $organizationNames[] = $reservation->organization->org_name;
            }

            return [
                'schedule_id' => 'reservation_' . $reservation->reservation_id,
                'title' => $reservation->activity_name ?: ($reservation->service?->service_name ?? 'Unknown Service'),
                'event_type' => strtolower(str_replace(' ', '_', $reservation->service?->service_category ?? 'other')),
                'mass_subtype' => null,
                'schedule_date' => $reservation->schedule_date->format('Y-m-d'),
                'start_time' => $reservation->schedule_date->format('H:i'),
                'end_time' => $reservation->end_time ? $reservation->end_time->format('H:i') : null,
                'location' => $reservation->custom_venue_name ?: ($reservation->venue?->name ?? null),
                'venue' => $reservation->venue ? ['name' => $reservation->venue->name] : null,
                'priest' => $priestName ? ['name' => $priestName] : null,
                'main_celebrant' => $mainCelebrant,
                'all_priests' => $allPriests,
                'organizations' => $organizationNames,
                'external_priest_name' => $reservation->external_priest_name,
                'external_priest_contact' => $reservation->external_priest_contact,
                'is_public' => true,
                'description' => $reservation->purpose ?: $reservation->details,
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
        $reservationSchedules = $reservations->map(function ($reservation) {
            // Determine priest names with main celebrant indication
            $priestName = null;
            $mainCelebrant = null;
            $allPriests = [];

            if ($reservation->priests && $reservation->priests->count() > 0) {
                $firstPriestName = null;
                foreach ($reservation->priests as $priest) {
                    $name = $priest->full_name;
                    if ($firstPriestName === null) {
                        $firstPriestName = $name;
                    }

                    $isMainCelebrant = ($priest->pivot->is_main_celebrant ?? false)
                        || ((int) $reservation->officiant_id === (int) $priest->id);

                    if ($isMainCelebrant) {
                        $mainCelebrant = $name;
                        $name .= ' (Main Celebrant)';
                    }
                    $allPriests[] = $name;
                }
                if ($mainCelebrant === null) {
                    $mainCelebrant = $firstPriestName;
                }
                $priestName = implode(', ', $allPriests);
            } elseif ($reservation->officiant) {
                $priestName = $reservation->officiant->full_name;
                $mainCelebrant = $priestName;
            } elseif ($reservation->external_priest_name) {
                $priestName = $reservation->external_priest_name . ' (External)';
                $mainCelebrant = $reservation->external_priest_name;
            }

            // Get organizations
            $organizationNames = [];
            if ($reservation->organizations && $reservation->organizations->count() > 0) {
                $organizationNames = $reservation->organizations->pluck('org_name')->toArray();
            } elseif ($reservation->organization) {
                $organizationNames[] = $reservation->organization->org_name;
            }

            return [
                'schedule_id' => 'reservation_' . $reservation->reservation_id,
                'title' => $reservation->activity_name ?: ($reservation->service?->service_name ?? 'Unknown Service'),
                'event_type' => strtolower(str_replace(' ', '_', $reservation->service?->service_category ?? 'other')),
                'schedule_date' => $reservation->schedule_date->format('Y-m-d'),
                'start_time' => $reservation->schedule_date->format('H:i'),
                'end_time' => $reservation->end_time ? $reservation->end_time->format('H:i') : null,
                'location' => $reservation->custom_venue_name ?: ($reservation->venue?->name ?? null),
                'priest' => $priestName ? ['name' => $priestName] : null,
                'main_celebrant' => $mainCelebrant,
                'all_priests' => $allPriests,
                'organizations' => $organizationNames,
                'external_priest_name' => $reservation->external_priest_name,
                'description' => $reservation->purpose ?: $reservation->details,
            ];
        });

        $schedules = $liturgicalSchedules->concat($reservationSchedules)->sortBy('start_time')->values();

        return response()->json($schedules);
    }
}
