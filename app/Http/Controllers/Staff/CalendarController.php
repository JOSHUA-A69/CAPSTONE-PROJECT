<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LiturgicalSchedule;
use App\Models\User;
use App\Models\Venue;
use App\Models\Service;
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
            ->with(['creator', 'priest', 'venue'])
            ->get();

        // Get all priests for the dropdown
        $priests = User::where('role', 'priest')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        // Get all venues for the dropdown
        $venues = Venue::orderBy('name')->get();
        
        // Get all services for mass type dropdowns
        $services = Service::orderBy('service_name')->get();

        return view('staff.calendar.index', compact('schedules', 'month', 'year', 'priests', 'venues', 'services'));
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
            'venue_id' => 'nullable|exists:venues,venue_id',
            'priest_id' => 'nullable|exists:users,id',
            'external_priest_name' => 'nullable|string|max:100',
            'external_priest_contact' => 'nullable|string|max:100',
            // Accept legacy event types to allow editing old records; new UI uses the first two
            'event_type' => 'required|in:institutional_mass,non_institutional_mass,mass,confession,adoration,retreat,seminar,meeting,celebration,other',
            // Mass subtype only required for the two new mass categories
            'mass_subtype' => 'nullable|required_if:event_type,institutional_mass,non_institutional_mass|string|max:255',
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_public'] = $request->has('is_public') ? true : false;

        // If external priest is specified, ensure priest_id is null; otherwise clear external fields
        if ($request->filled('external_priest_name')) {
            $validated['priest_id'] = null;
        } elseif ($request->filled('priest_id')) {
            $validated['external_priest_name'] = null;
            $validated['external_priest_contact'] = null;
        }

        // Normalize legacy 'mass' to 'institutional_mass' by default if needed
        if ($validated['event_type'] === 'mass') {
            $validated['event_type'] = 'institutional_mass';
        }

        // If event_type is not one of the two mass categories, clear mass_subtype
        if (!in_array($validated['event_type'], ['institutional_mass', 'non_institutional_mass'])) {
            $validated['mass_subtype'] = null;
        }

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
            'venue_id' => 'nullable|exists:venues,venue_id',
            'priest_id' => 'nullable|exists:users,id',
            'external_priest_name' => 'nullable|string|max:100',
            'external_priest_contact' => 'nullable|string|max:100',
            'event_type' => 'required|in:institutional_mass,non_institutional_mass,mass,confession,adoration,retreat,seminar,meeting,celebration,other',
            'mass_subtype' => 'nullable|required_if:event_type,institutional_mass,non_institutional_mass|string|max:255',
            'is_public' => 'boolean',
        ]);

        $validated['is_public'] = $request->has('is_public') ? true : false;

        // Mutual exclusivity for priest fields
        if ($request->filled('external_priest_name')) {
            $validated['priest_id'] = null;
        } elseif ($request->filled('priest_id')) {
            $validated['external_priest_name'] = null;
            $validated['external_priest_contact'] = null;
        }

        // Normalize legacy 'mass'
        if ($validated['event_type'] === 'mass') {
            $validated['event_type'] = 'institutional_mass';
        }

        if (!in_array($validated['event_type'], ['institutional_mass', 'non_institutional_mass'])) {
            $validated['mass_subtype'] = null;
        }

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
