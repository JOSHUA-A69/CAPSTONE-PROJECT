<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest;
use App\Models\Organization;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':requestor']);
    }

    public function index()
    {
        $reservations = Reservation::with(['service', 'venue', 'organization'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('requestor.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $services = Service::orderBy('service_name')->get();
        $venues = Venue::orderBy('name')->get();
        // Optional: only organizations this user belongs to; for now list all
        $organizations = Organization::orderBy('org_name')->get();
        return view('requestor.reservations.create', compact('services', 'venues', 'organizations'));
    }

    public function store(ReservationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        $reservation = Reservation::create($data);

        // history
        $reservation->history()->create([
            'performed_by' => Auth::id(),
            'action' => 'created',
            'remarks' => 'Reservation submitted by requestor',
            'performed_at' => now(),
        ]);

        return Redirect::route('requestor.reservations.index')->with('status', 'reservation-submitted');
    }
}
