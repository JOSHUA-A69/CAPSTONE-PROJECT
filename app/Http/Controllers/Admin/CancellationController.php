<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationCancellation;
use App\Models\ReservationHistory;
use App\Models\Notification;
use App\Models\User;
use App\Mail\ReservationCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CancellationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin']);
    }

    /**
     * Show cancellation details
     */
    public function show($id)
    {
        $cancellation = ReservationCancellation::with([
            'reservation.organization',
            'reservation.assignedPriest',
            'requestor',
            'staffConfirmer',
            'adminConfirmer',
            'adviserConfirmer',
            'priestConfirmer'
        ])->findOrFail($id);

        return view('admin.cancellations.show', compact('cancellation'));
    }

    /**
     * Confirm cancellation
     */
    public function confirm($id)
    {
        $cancellation = ReservationCancellation::with('reservation')->findOrFail($id);

        // Check if already confirmed by this admin
        if ($cancellation->isAdminConfirmed()) {
            return redirect()->route('admin.cancellations.show', $id)
                ->with('info', 'You have already confirmed this cancellation.');
        }

        // Mark as confirmed by admin
        $cancellation->update([
            'admin_confirmed_at' => now(),
            'admin_confirmed_by' => Auth::id(),
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $cancellation->reservation_id,
            'action' => 'cancellation_confirmed_by_admin',
            'details' => 'Cancellation confirmed by admin ' . Auth::user()->name,
            'performed_by' => Auth::id(),
        ]);

        // Check if all required confirmations are done
        if ($cancellation->isFullyConfirmed()) {
            $this->completeCancellation($cancellation);
        }

        return redirect()->route('admin.cancellations.show', $id)
            ->with('success', 'Cancellation confirmed successfully.');
    }

    /**
     * Complete the cancellation process
     */
    private function completeCancellation(ReservationCancellation $cancellation)
    {
        $reservation = $cancellation->reservation;
        
        // Update cancellation status
        $cancellation->update([
            'status' => 'completed',
        ]);

        // Update reservation status
        $reservation->update([
            'status' => 'cancelled',
        ]);

        // Add to history
        ReservationHistory::create([
            'reservation_id' => $reservation->reservation_id,
            'action' => 'cancellation_completed',
            'details' => 'Reservation cancelled - all parties confirmed',
            'performed_by' => Auth::id(),
        ]);

        // Notify requestor
        $this->notifyRequestor($reservation, $cancellation);
        
        // Notify all admins (except the one who confirmed)
        $this->notifyAdmins($reservation, $cancellation);
    }
    
    /**
     * Notify requestor about cancellation completion
     */
    private function notifyRequestor($reservation, $cancellation)
    {
        $requestor = $reservation->requestor;
        
        if (!$requestor) {
            return;
        }
        
        // Create in-app notification
        Notification::create([
            'user_id' => $requestor->id,
            'reservation_id' => $reservation->reservation_id,
            'type' => 'Cancellation Completed',
            'message' => 'Your reservation for <strong>' . $reservation->service->service_name . '</strong> on ' 
                . $reservation->schedule_date->format('M d, Y h:i A') . ' has been cancelled.',
            'sent_at' => now(),
            'data' => json_encode([
                'service_name' => $reservation->service->service_name,
                'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                'cancellation_reason' => $cancellation->reason ?? 'No reason provided',
            ]),
        ]);
        
        // Send email
        try {
            $cancelledBy = Auth::user()->full_name ?? 'Admin';
            Mail::to($requestor->email)->send(
                new ReservationCancelled(
                    $reservation, 
                    $cancellation->reason ?? 'Cancellation confirmed by all parties',
                    $cancelledBy
                )
            );
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation email to requestor: ' . $e->getMessage());
        }
    }
    
    /**
     * Notify all admins about cancellation completion
     */
    private function notifyAdmins($reservation, $cancellation)
    {
        // Get all admins except the one who confirmed
        $admins = User::where('role', 'admin')
            ->where('status', 'active')
            ->where('id', '!=', Auth::id())
            ->get();
        
        foreach ($admins as $admin) {
            // Create in-app notification
            Notification::create([
                'user_id' => $admin->id,
                'reservation_id' => $reservation->reservation_id,
                'type' => 'Cancellation Completed',
                'message' => 'Reservation for <strong>' . $reservation->service->service_name . '</strong> on ' 
                    . $reservation->schedule_date->format('M d, Y h:i A') . ' has been cancelled.',
                'sent_at' => now(),
                'data' => json_encode([
                    'service_name' => $reservation->service->service_name,
                    'schedule_date' => $reservation->schedule_date->format('Y-m-d H:i:s'),
                    'requestor_name' => $reservation->requestor->full_name ?? 'Unknown',
                    'cancellation_reason' => $cancellation->reason ?? 'No reason provided',
                ]),
            ]);
            
            // Send email
            try {
                $cancelledBy = Auth::user()->full_name ?? 'Admin';
                Mail::to($admin->email)->send(
                    new ReservationCancelled(
                        $reservation, 
                        $cancellation->reason ?? 'Cancellation confirmed by all parties',
                        $cancelledBy
                    )
                );
            } catch (\Exception $e) {
                Log::error('Failed to send cancellation email to admin: ' . $e->getMessage());
            }
        }
    }
}
