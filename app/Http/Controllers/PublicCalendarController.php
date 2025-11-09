<?php

namespace App\Http\Controllers;

use App\Models\LiturgicalSchedule;
use Illuminate\Http\Request;

class PublicCalendarController extends Controller
{
    /**
     * Display the public calendar view
     */
    public function index(Request $request)
    {
        // Get all public schedules with priest and venue relationships (FullCalendar will handle date filtering)
        $schedules = LiturgicalSchedule::with(['priest:id', 'venue:venue_id,name'])
            ->public()
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        // Get upcoming schedules (next 5 events)
        $upcomingSchedules = LiturgicalSchedule::with(['priest:id', 'venue:venue_id,name'])
            ->public()
            ->upcoming()
            ->limit(5)
            ->get();

        return view('calendar.public', compact('schedules', 'upcomingSchedules'));
    }

    /**
     * Get public schedules for a specific date (AJAX)
     */
    public function getSchedules(Request $request)
    {
        $date = $request->get('date');
        $schedules = LiturgicalSchedule::with(['priest', 'venue'])
            ->public()
            ->where('schedule_date', $date)
            ->orderBy('start_time')
            ->get();

        return response()->json($schedules);
    }
}
