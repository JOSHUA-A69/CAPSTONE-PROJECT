<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\ReservationHistory;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function archive(Request $request, $historyId)
    {
        $history = ReservationHistory::findOrFail($historyId);
        
        // Verify the user has permission to archive this history
        $reservation = $history->reservation;
        if ($reservation->officiant_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history->update([
            'archived_at' => now(),
            'archived_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'History item archived successfully']);
    }

    public function clearAll(Request $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        
        // Verify the user has permission
        if ($reservation->officiant_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $updated = ReservationHistory::where('reservation_id', $reservationId)
            ->whereNull('archived_at')
            ->update([
                'archived_at' => now(),
                'archived_by' => Auth::id(),
            ]);

        return response()->json(['success' => true, 'message' => 'All history items archived', 'count' => $updated]);
    }

    public function archived(Request $request)
    {
        // Get all archived history for reservations assigned to this priest
        $archivedHistory = ReservationHistory::archived()
            ->whereHas('reservation', function($query) {
                $query->where('officiant_id', Auth::id());
            })
            ->with(['reservation', 'performedBy', 'archivedBy'])
            ->orderBy('archived_at', 'desc')
            ->paginate(20);

        return view('priest.history.archived', compact('archivedHistory'));
    }

    public function restore(Request $request, $historyId)
    {
        $history = ReservationHistory::findOrFail($historyId);
        
        // Verify the user has permission
        $reservation = $history->reservation;
        if ($reservation->officiant_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history->update([
            'archived_at' => null,
            'archived_by' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'History item restored successfully']);
    }
}

