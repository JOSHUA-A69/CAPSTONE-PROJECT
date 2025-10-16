<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
    }

    public function index()
    {
        $search = request('q');
        $status = request('status');

        $query = Reservation::with(['user', 'service', 'venue', 'organization']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purpose', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $reservations = $query->orderByDesc('created_at')->paginate(20)->appends(request()->only('q', 'status'));

        $statuses = ['pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected', 'cancelled'];

        return view('staff.reservations.index', compact('reservations', 'statuses', 'search', 'status'));
    }

    public function show($reservation_id)
    {
        $reservation = Reservation::with(['user', 'service', 'venue', 'organization', 'history.reservation'])
            ->findOrFail($reservation_id);

        $priests = User::where('role', 'priest')->orderBy('first_name')->get();

        return view('staff.reservations.show', compact('reservation', 'priests'));
    }

    public function approve(Request $request, $reservation_id)
    {
        $reservation = Reservation::findOrFail($reservation_id);
        $reservation->update(['status' => 'admin_approved']);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'admin_approved',
            'remarks' => $request->input('remarks', 'Approved by staff/admin'),
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-approved');
    }

    public function cancel(Request $request, $reservation_id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reservation = Reservation::findOrFail($reservation_id);
        $reservation->update(['status' => 'cancelled']);

        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'cancelled',
            'remarks' => 'Cancelled by staff: ' . $request->input('reason'),
            'performed_at' => now(),
        ]);

        return Redirect::back()->with('status', 'reservation-cancelled');
    }
}
