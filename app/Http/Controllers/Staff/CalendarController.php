<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\LiturgicalSchedule;
use App\Models\User;
use App\Models\Venue;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Notifications as NotificationHelper;

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
            // Mass subtype optional; UI may leave this empty
            'mass_subtype' => 'nullable|string|max:255',
            'is_public' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
    // Normalize public flag reliably (hidden 0 + checkbox 1 pattern or missing field)
    $validated['is_public'] = $request->boolean('is_public');

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

        $schedule = LiturgicalSchedule::create($validated);

        // Notify assigned priest if any
        if (!empty($validated['priest_id'])) {
            $priest = User::find($validated['priest_id']);
            if ($priest) {
                $title = $validated['title'] ?? ($validated['event_type'] ?? 'Schedule');
                $when = $validated['schedule_date'] . (isset($validated['start_time']) ? ' ' . $validated['start_time'] : '');
                $venueName = null;
                if (!empty($validated['venue_id'])) {
                    $venueName = optional(Venue::find($validated['venue_id']))->name;
                }

                NotificationHelper::make([
                    'user_id' => $priest->id,
                    'message' => '<strong>CREaM Staff</strong> assigned you to a schedule: <em>' . e($title) . '</em> on <strong>' . e($when) . '</strong>' . ($venueName ? ' at <strong>' . e($venueName) . '</strong>' : ''),
                    'type' => NotificationHelper::TYPE_SCHEDULE_ASSIGNMENT,
                    'data' => [
                        'schedule_id' => $schedule->schedule_id,
                        'event_type' => $schedule->event_type,
                        'schedule_date' => $schedule->schedule_date,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'venue' => $venueName ?? $schedule->location,
                        'title' => $schedule->title,
                        'created_by' => Auth::id(),
                    ],
                ]);
            }
        }

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
            'mass_subtype' => 'nullable|string|max:255',
            'is_public' => 'boolean',
        ]);

    $validated['is_public'] = $request->boolean('is_public');

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

        // Track original priest to detect reassignment
        $originalPriestId = $schedule->priest_id;

        $schedule->update($validated);

        // Notify newly assigned priest if changed or set
        if (!empty($validated['priest_id']) && $validated['priest_id'] != $originalPriestId) {
            $priest = User::find($validated['priest_id']);
            if ($priest) {
                $title = $schedule->title ?? ($schedule->event_type ?? 'Schedule');
                $when = $schedule->schedule_date . ($schedule->start_time ? ' ' . $schedule->start_time : '');
                $venueName = $schedule->venue->name ?? $schedule->location;

                NotificationHelper::make([
                    'user_id' => $priest->id,
                    'message' => '<strong>CREaM Staff</strong> assigned you to a schedule: <em>' . e($title) . '</em> on <strong>' . e($when) . '</strong>' . ($venueName ? ' at <strong>' . e($venueName) . '</strong>' : ''),
                    'type' => NotificationHelper::TYPE_SCHEDULE_ASSIGNMENT,
                    'data' => [
                        'schedule_id' => $schedule->schedule_id,
                        'event_type' => $schedule->event_type,
                        'schedule_date' => $schedule->schedule_date,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'venue' => $venueName,
                        'title' => $schedule->title,
                        'updated_by' => Auth::id(),
                    ],
                ]);
            }
        }

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
