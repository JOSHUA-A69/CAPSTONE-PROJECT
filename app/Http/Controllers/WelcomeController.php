<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        // Fetch upcoming reservations (exclude cancelled/rejected) with related info
        $upcomingReservations = Reservation::with(['service', 'venue', 'organization', 'officiant'])
            ->upcoming()
            ->whereNotIn('status', ['cancelled', 'rejected'])
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

        return view('welcome', compact('upcomingReservations', 'currentMonth'));
    }
}
