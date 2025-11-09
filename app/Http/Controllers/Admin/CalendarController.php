<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\LiturgicalSchedule;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    /**
     * Unified admin calendar: all upcoming reservations (excluding cancelled/rejected),
     * all upcoming staff-plotted schedules, and highlight those where admin presides.
     */
    public function index()
    {
        // All upcoming reservations across the system except cancelled/rejected
        $reservations = Reservation::with(['service:service_id,service_name', 'venue:venue_id,name'])
            ->whereDate('schedule_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->orderBy('schedule_date')
            ->get(['reservation_id','service_id','venue_id','custom_venue_name','schedule_date','status','participants_count','activity_name','officiant_id']);

        // All upcoming staff-plotted schedules
        $schedules = LiturgicalSchedule::with(['priest:id', 'venue:venue_id,name'])
            ->upcoming()
            ->get(['schedule_id','title','event_type','schedule_date','start_time','end_time','location','venue_id','priest_id','is_public']);

        $adminId = Auth::id();

        return view('admin.calendar.index', compact('reservations', 'schedules', 'adminId'));
    }
}
