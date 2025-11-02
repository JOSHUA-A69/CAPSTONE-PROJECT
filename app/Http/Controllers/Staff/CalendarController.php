<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LiturgicalSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display the calendar management interface
     */
    public function index(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get all schedules (not limited to current month) so FullCalendar can display them when navigating
        $schedules = LiturgicalSchedule::orderBy('schedule_date')
            ->orderBy('start_time')
            ->with(['creator', 'priest'])
            ->get();

        // Get all priests for the dropdown
        $priests = User::where('role', 'priest')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return view('staff.calendar.index', compact('schedules', 'month', 'year', 'priests'));
    }

    /**
     * Get schedules for a specific date (AJAX)
     */
    public function getSchedules(Request $request)
    {
        $date = $request->get('date');
        $schedules = LiturgicalSchedule::where('schedule_date', $date)
            ->orderBy('start_time')
            ->with('creator')
            ->get();

        return response()->json($schedules);
    }

    /**
     * Store a new schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'schedule_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'priest_id' => 'nullable|exists:users,id',
            'event_type' => 'required|in:mass,confession,adoration,retreat,seminar,meeting,celebration,other',
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_public'] = $request->has('is_public') ? true : false;

        LiturgicalSchedule::create($validated);

        return redirect()->route('staff.calendar.index')
            ->with('success', 'Schedule added successfully!');
    }

    /**
     * Update an existing schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = LiturgicalSchedule::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'schedule_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'priest_id' => 'nullable|exists:users,id',
            'event_type' => 'required|in:mass,confession,adoration,retreat,seminar,meeting,celebration,other',
            'is_public' => 'boolean',
        ]);

        $validated['is_public'] = $request->has('is_public') ? true : false;

        $schedule->update($validated);

        return redirect()->route('staff.calendar.index')
            ->with('success', 'Schedule updated successfully!');
    }

    /**
     * Delete a schedule
     */
    public function destroy($id)
    {
        $schedule = LiturgicalSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('staff.calendar.index')
            ->with('success', 'Schedule deleted successfully!');
    }
}
