<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
    }

    public function index()
    {
        // Get organizations where this adviser is assigned
        $adviserOrgs = Auth::user()->organizations->pluck('org_id');

        // Get reservations linked to those organizations
        $reservations = Reservation::with(['user', 'service', 'venue', 'organization'])
            ->whereIn('org_id', $adviserOrgs)
            ->whereIn('status', ['pending', 'adviser_approved', 'rejected'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('adviser.reservations.index', compact('reservations'));
    }

    public function approve(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);

        // Verify this adviser is assigned to the reservation's organization
        if (!Auth::user()->organizations->pluck('org_id')->contains($reservation->org_id)) {
            abort(403, 'You are not the adviser for this organization.');
        }

        $reservation->update(['status' => 'adviser_approved']);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'adviser_approved',
            'remarks' => $request->input('remarks', 'Approved by adviser'),
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-approved');
    }

    public function reject(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);

        // Verify this adviser is assigned to the reservation's organization
        if (!Auth::user()->organizations->pluck('org_id')->contains($reservation->org_id)) {
            abort(403, 'You are not the adviser for this organization.');
        }

        $reservation->update(['status' => 'rejected']);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'rejected',
            'remarks' => 'Rejected by adviser: ' . $request->input('reason'),
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-rejected');
    }
}
