<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use App\Models\Venue;
use App\Models\LiturgicalSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        // Caching for 5 minutes (300 seconds) to reduce DB load
        // Cached data includes reservations, schedules, services, venues
        // Cache key depends on the month filter if present
        $cacheKey = 'welcome_page_data_v3_' . ($request->query('month') ?? 'current');

        $data = Cache::remember($cacheKey, 300, function () use ($request) {
            // Fetch upcoming reservations (only necessary columns)
            $upcomingReservations = Reservation::with([
                    'service:service_id,service_name,service_category',
                    'venue:venue_id,name',
                    'organization:org_id,org_name',
                    'organizations:org_id,org_name',
                    'officiant:id,first_name,middle_name,last_name',
                    'priests:id,first_name,middle_name,last_name'
                ])
                ->select([
                    'reservation_id', 'service_id', 'venue_id', 'org_id', 'officiant_id',
                    'schedule_date', 'status', 'activity_name', 'custom_venue_name', 'external_priest_name', 'priest_selection_type'
                ])
                ->upcoming()
                ->whereIn('status', ['admin_approved', 'approved', 'confirmed'])
                ->where('schedule_date', '<=', Carbon::now()->addMonths(6))
                ->orderBy('schedule_date')
                ->get();

            // Public calendar should never expose reservation details.
            $upcomingReservations->transform(function ($reservation) {
                $isPrivateReservation = in_array($reservation->status, ['admin_approved', 'approved', 'confirmed'], true);

                $reservation->setAttribute('is_private_reservation', $isPrivateReservation);
                $reservation->setAttribute('public_title', $isPrivateReservation
                    ? 'Occupied'
                    : ($reservation->activity_name ?: ($reservation->service?->service_name ?? 'Reservation')));

                return $reservation;
            });

            // Fetch liturgical schedules
            $allSchedules = LiturgicalSchedule::with(['priest:id,first_name,last_name', 'venue:venue_id,name'])
                ->where('is_public', 1)
                ->where('schedule_date', '>=', Carbon::now()->startOfDay())
                ->where('schedule_date', '<=', Carbon::now()->addMonths(6))
                ->orderBy('schedule_date')
                ->orderBy('start_time')
                ->get();

            // Filter options: all services and all venues (db)
            $services = Service::orderBy('service_name')->get(['service_id','service_name']);
            $venues = Venue::orderBy('name')->get(['venue_id','name']);

            return compact('upcomingReservations', 'allSchedules', 'services', 'venues');
        });

        // Current month for the mini-calendar grid (supports ?month=YYYY-MM)
        // This is purely view logic, no need to cache the Date object itself
        $param = $request->query('month');
        if ($param && preg_match('/^\d{4}-\d{2}$/', $param)) {
            try {
                $currentMonth = Carbon::createFromFormat('Y-m-d', $param.'-01');
            } catch (\Exception $e) {
                $currentMonth = Carbon::now();
            }
        } else {
            $currentMonth = Carbon::now();
        }

        return view('welcome', array_merge($data, ['currentMonth' => $currentMonth]));
    }
}
