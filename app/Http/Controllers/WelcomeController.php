<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Service;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        // Fetch upcoming reservations (exclude cancelled/rejected) with related info
        // Optimizing performance: limit to next 6 months
        $upcomingReservations = Reservation::with(['service:service_id,service_name', 'venue:venue_id,name', 'organization:org_id,org_name', 'officiant:id,first_name,last_name'])
            ->upcoming()
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->where('schedule_date', '<=', Carbon::now()->addMonths(6))
            ->orderBy('schedule_date')
            ->get();

        // Current month for the mini-calendar grid (supports ?month=YYYY-MM)
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

        // Filter options: all services and all venues (db)
        $services = Service::orderBy('service_name')->get(['service_id','service_name']);
        $venues = Venue::orderBy('name')->get(['venue_id','name']);

        return view('welcome', compact('upcomingReservations', 'currentMonth', 'services', 'venues'));
    }
}
