#!/usr/bin/env php
<?php

/**
 * Generate a comprehensive set of reservation scenarios with notifications
 * - Successful (approved)
 * - Unattended (pending >24h, follow-up)
 * - Adviser Rejected
 * - Marked Unavailable by Staff (staff rejection)
 * - Cancelled by Staff
 * - Cancelled by Admin
 * - Priest Declines
 * - New Priest Assigned (reassignment)
 * - Priest Undo Decline (undecline)
 * - Priest Cancels Confirmation (after approving)
 * - Requestor Cancels Request
 * - Rescheduling (change request -> approved)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Reservation;
use App\Models\ReservationHistory;
use App\Models\ReservationChange;
use App\Models\ReservationCancellation;
use App\Models\PriestDecline;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Venue;
use App\Services\ReservationNotificationService;
use App\Services\CancellationNotificationService;
use App\Support\Notifications as Notif;
use Illuminate\Support\Str;

function out($s=''){ echo $s."\n"; }

out("\n╔════════════════════════════════════════════════════════════╗");
out("║  GENERATE RESERVATION SCENARIOS - eReligiousServices     ║");
out("╚════════════════════════════════════════════════════════════╝\n");

$notify = new ReservationNotificationService();
$cancelNotify = new CancellationNotificationService();

// Ensure base data
$requestor = User::where('role','requestor')->where('status','active')->first();
$adviser = User::where('role','adviser')->where('status','active')->first();
$admin = User::whereIn('role',['admin','staff'])->where('status','active')->first();
$org = Organization::first();
$service = Service::first();
$venue = Venue::first();

if (!$requestor || !$adviser || !$admin || !$org || !$service) {
    out('❌ Missing seed data.');
    exit(1);
}

// Ensure we have two priests for reassignment scenario
$priests = User::where('role','priest')->where('status','active')->take(2)->get();
if ($priests->count() < 2) {
    $extraPriest = User::firstOrCreate(
        ['email' => 'fr.new.priest+'.time().'@example.com'],
        [
            'first_name' => 'New', 'last_name' => 'Priest', 'role' => 'priest',
            'password' => bcrypt('password'), 'status' => 'active', 'email_verified_at' => now()
        ]
    );
    $priests = User::where('role','priest')->where('status','active')->take(2)->get();
}
$priest1 = $priests[0];
$priest2 = $priests[1] ?? $priests[0];

$now = now();
$ids = [];

$make = function(string $label, int $daysAhead = 3) use ($requestor,$org,$service,$venue,$now) {
    $dt = $now->copy()->addDays($daysAhead)->setTime(10, 0);
    $r = Reservation::create([
        'user_id' => $requestor->id,
        'org_id' => $org->org_id,
        'venue_id' => optional($venue)->venue_id,
        'service_id' => $service->service_id,
        'schedule_date' => $dt,
        'status' => 'pending',
        'purpose' => $label,
        'details' => 'Scenario test: ' . $label,
    ]);
    ReservationHistory::create([
        'reservation_id' => $r->reservation_id,
        'performed_by' => $requestor->id,
        'action' => 'created',
        'remarks' => $label,
        'performed_at' => now(),
    ]);
    $r->refresh();
    return $r;
};

// 1) Successful (approved)
$r_success = $make('Scenario: Successful (approved)', 3);
$notify->notifyReservationSubmitted($r_success);
$r_success->update(['adviser_notified_at'=>now(),'status'=>'adviser_approved','adviser_responded_at'=>now()]);
ReservationHistory::create(['reservation_id'=>$r_success->reservation_id,'performed_by'=>$adviser->id,'action'=>'adviser_approved','remarks'=>'Auto-approve','performed_at'=>now()]);
$notify->notifyAdviserApproved($r_success, 'Auto-approve');
$r_success->update(['officiant_id'=>$priest1->id,'status'=>'admin_approved','priest_notified_at'=>now(),'priest_confirmation'=>'pending']);
ReservationHistory::create(['reservation_id'=>$r_success->reservation_id,'performed_by'=>$admin->id,'action'=>'priest_assigned','remarks'=>'Assigned P1','performed_at'=>now()]);
$notify->notifyPriestAssigned($r_success);
$r_success->update(['priest_confirmation'=>'confirmed','priest_confirmed_at'=>now(),'status'=>'approved']);
ReservationHistory::create(['reservation_id'=>$r_success->reservation_id,'performed_by'=>$priest1->id,'action'=>'priest_confirmed','remarks'=>'Confirmed','performed_at'=>now()]);
$notify->notifyPriestConfirmed($r_success, $priest1->id);
$ids['successful'] = $r_success->reservation_id;

// 2) Unattended (pending >24h, follow-up)
$r_unatt = $make('Scenario: Unattended (follow-up)', 6);
$notify->notifyReservationSubmitted($r_unatt);
// Backdate created_at to > 2 days ago
$r_unatt->created_at = now()->subDays(2);
$r_unatt->adviser_notified_at = now()->subDays(2);
$r_unatt->save();
$notify->notifyAdviserFollowUp($r_unatt);
$r_unatt->update(['staff_followed_up_at'=>now()]);
$ids['unattended'] = $r_unatt->reservation_id;

// 3) Adviser Rejected
$r_adv_rej = $make('Scenario: Adviser Rejected', 7);
$notify->notifyReservationSubmitted($r_adv_rej);
$r_adv_rej->update(['adviser_notified_at'=>now(),'status'=>'rejected','adviser_responded_at'=>now()]);
ReservationHistory::create(['reservation_id'=>$r_adv_rej->reservation_id,'performed_by'=>$adviser->id,'action'=>'adviser_rejected','remarks'=>'Reason: conflict','performed_at'=>now()]);
$notify->notifyAdviserRejected($r_adv_rej, 'Schedule conflict at venue');
$ids['adviser_rejected'] = $r_adv_rej->reservation_id;

// 4) Marked Unavailable by Staff (treat as staff rejection)
$r_unavail = $make('Scenario: Staff Marked Unavailable', 8);
$notify->notifyReservationSubmitted($r_unavail);
$r_unavail->update(['adviser_notified_at'=>now(),'adviser_responded_at'=>now(),'status'=>'adviser_approved']);
ReservationHistory::create(['reservation_id'=>$r_unavail->reservation_id,'performed_by'=>$adviser->id,'action'=>'adviser_approved','remarks'=>'OK','performed_at'=>now()]);
// Staff rejects due to unavailability
$r_unavail->update(['status'=>'rejected']);
ReservationHistory::create(['reservation_id'=>$r_unavail->reservation_id,'performed_by'=>$admin->id,'action'=>'status_updated','remarks'=>'Staff marked date unavailable','performed_at'=>now()]);
// Reuse admin-reject notifier with staff actor name
$notify->notifyAdminRejected($r_unavail, 'Date unavailable / blocked', $admin);
$ids['staff_unavailable'] = $r_unavail->reservation_id;

// 5) Cancelled by Staff
$r_cancel_staff = $make('Scenario: Cancelled by Staff', 9);
$notify->notifyReservationSubmitted($r_cancel_staff);
$r_cancel_staff->update(['status'=>'cancelled','cancellation_reason'=>'Internal ops issue','cancelled_by'=>$admin->id]);
ReservationHistory::create(['reservation_id'=>$r_cancel_staff->reservation_id,'performed_by'=>$admin->id,'action'=>'cancelled','remarks'=>'Cancelled by staff','performed_at'=>now()]);
$notify->notifyCancellation($r_cancel_staff, 'Internal operations issue', $admin->first_name.' '.$admin->last_name);
$ids['cancelled_by_staff'] = $r_cancel_staff->reservation_id;

// 6) Cancelled by Admin
$r_cancel_admin = $make('Scenario: Cancelled by Admin', 10);
$notify->notifyReservationSubmitted($r_cancel_admin);
$r_cancel_admin->update(['status'=>'cancelled','cancellation_reason'=>'Policy violation','cancelled_by'=>$admin->id]);
ReservationHistory::create(['reservation_id'=>$r_cancel_admin->reservation_id,'performed_by'=>$admin->id,'action'=>'cancelled','remarks'=>'Cancelled by admin','performed_at'=>now()]);
$notify->notifyCancellation($r_cancel_admin, 'Policy violation', 'Administrator');
$ids['cancelled_by_admin'] = $r_cancel_admin->reservation_id;

// 7) Priest Declines (pending priest reassignment)
$r_decline = $make('Scenario: Priest Declines', 11);
$notify->notifyReservationSubmitted($r_decline);
$r_decline->update(['status'=>'admin_approved','officiant_id'=>$priest1->id,'priest_notified_at'=>now(),'priest_confirmation'=>'pending']);
ReservationHistory::create(['reservation_id'=>$r_decline->reservation_id,'performed_by'=>$admin->id,'action'=>'priest_assigned','remarks'=>'Assigned P1','performed_at'=>now()]);
$notify->notifyPriestAssigned($r_decline);
// Decline
PriestDecline::create([
    'reservation_id'=>$r_decline->reservation_id,
    'priest_id'=>$priest1->id,
    'reason'=>'Schedule conflict',
    'declined_at'=>now(),
    'reservation_activity_name'=>$r_decline->activity_name ?? $r_decline->service->service_name,
    'reservation_schedule_date'=>$r_decline->schedule_date,
    'reservation_venue'=>$r_decline->custom_venue_name ?? $r_decline->venue->name ?? 'N/A',
]);
$r_decline->update(['priest_confirmation'=>'declined','priest_confirmed_at'=>now(),'status'=>'pending_priest_reassignment','officiant_id'=>null]);
ReservationHistory::create(['reservation_id'=>$r_decline->reservation_id,'performed_by'=>$priest1->id,'action'=>'priest_declined','remarks'=>'Declined by priest','performed_at'=>now()]);
$notify->notifyPriestDeclined($r_decline, 'Schedule conflict', $priest1->id);
$ids['priest_declined'] = $r_decline->reservation_id;

// 8) New Priest Assigned (after decline)
$r_reassign = $r_decline; // use same reservation
$r_reassign->update(['officiant_id'=>$priest2->id,'status'=>'admin_approved','priest_notified_at'=>now(),'priest_confirmation'=>'pending']);
ReservationHistory::create(['reservation_id'=>$r_reassign->reservation_id,'performed_by'=>$admin->id,'action'=>'priest_reassigned','remarks'=>'Reassigned to P2','performed_at'=>now()]);
$notify->notifyPriestAssigned($r_reassign);
$ids['new_priest_assigned'] = $r_reassign->reservation_id;

// 9) Priest Undo Decline (same priest1 restores assignment on a different reservation)
$r_undecline = $make('Scenario: Priest Undo Decline', 12);
// Simulate initial assign then priest1 declined
$r_undecline->update(['status'=>'admin_approved','officiant_id'=>$priest1->id,'priest_notified_at'=>now(),'priest_confirmation'=>'pending']);
ReservationHistory::create(['reservation_id'=>$r_undecline->reservation_id,'performed_by'=>$admin->id,'action'=>'priest_assigned','remarks'=>'Assigned P1','performed_at'=>now()]);
$notify->notifyPriestAssigned($r_undecline);
PriestDecline::create([
    'reservation_id'=>$r_undecline->reservation_id,
    'priest_id'=>$priest1->id,
    'reason'=>'Travel',
    'declined_at'=>now(),
    'reservation_activity_name'=>$r_undecline->activity_name ?? $r_undecline->service->service_name,
    'reservation_schedule_date'=>$r_undecline->schedule_date,
    'reservation_venue'=>$r_undecline->custom_venue_name ?? $r_undecline->venue->name ?? 'N/A',
]);
$r_undecline->update(['officiant_id'=>null,'priest_confirmation'=>'declined','priest_confirmed_at'=>now(),'status'=>'pending_priest_reassignment']);
ReservationHistory::create(['reservation_id'=>$r_undecline->reservation_id,'performed_by'=>$priest1->id,'action'=>'priest_declined','remarks'=>'Declined','performed_at'=>now()]);
// Now priest undeclines (restores assignment)
$r_undecline->update(['officiant_id'=>$priest1->id,'priest_confirmation'=>null,'priest_confirmed_at'=>null,'status'=>'pending_priest_confirmation']);
ReservationHistory::create(['reservation_id'=>$r_undecline->reservation_id,'performed_by'=>$priest1->id,'action'=>'priest_reassigned','remarks'=>'Undeclined: restored assignment','performed_at'=>now()]);
$notify->notifyPriestUndeclined($r_undecline, $priest1->id);
$ids['priest_undecline'] = $r_undecline->reservation_id;

// 10) Priest Cancels Confirmation (had approved earlier)
$r_cancel_conf = $make('Scenario: Priest Cancels Confirmation', 13);
$r_cancel_conf->update(['status'=>'admin_approved','officiant_id'=>$priest1->id,'priest_notified_at'=>now(),'priest_confirmation'=>'pending']);
ReservationHistory::create(['reservation_id'=>$r_cancel_conf->reservation_id,'performed_by'=>$admin->id,'action'=>'priest_assigned','remarks'=>'Assigned P1','performed_at'=>now()]);
$notify->notifyPriestAssigned($r_cancel_conf);
// Confirm
$r_cancel_conf->update(['priest_confirmation'=>'confirmed','priest_confirmed_at'=>now(),'status'=>'approved']);
ReservationHistory::create(['reservation_id'=>$r_cancel_conf->reservation_id,'performed_by'=>$priest1->id,'action'=>'priest_confirmed','remarks'=>'Confirmed','performed_at'=>now()]);
$notify->notifyPriestConfirmed($r_cancel_conf, $priest1->id);
// Then cancel confirmation
$r_cancel_conf->update(['officiant_id'=>null,'priest_confirmation'=>'declined','priest_confirmed_at'=>now(),'status'=>'pending_priest_reassignment']);
ReservationHistory::create(['reservation_id'=>$r_cancel_conf->reservation_id,'performed_by'=>$priest1->id,'action'=>'priest_cancelled_confirmation','remarks'=>'Cancelled confirmation: emergency','performed_at'=>now()]);
$notify->notifyPriestCancelledConfirmation($r_cancel_conf, 'Emergency conflict', $priest1->id);
$ids['priest_cancel_confirmation'] = $r_cancel_conf->reservation_id;

// 11) Requestor Cancels Request
$r_req_cancel = $make('Scenario: Requestor Cancels', 14);
$notify->notifyReservationSubmitted($r_req_cancel);
$r_req_cancel->update(['status'=>'cancelled','cancellation_reason'=>'Changed plans','cancelled_by'=>$requestor->id]);
ReservationHistory::create(['reservation_id'=>$r_req_cancel->reservation_id,'performed_by'=>$requestor->id,'action'=>'cancelled','remarks'=>'Requestor cancelled','performed_at'=>now()]);
$notify->notifyCancellation($r_req_cancel, 'Requestor changed plans', $requestor->first_name.' '.$requestor->last_name);
$ids['requestor_cancelled'] = $r_req_cancel->reservation_id;

// 12) Rescheduling (change request -> approved)
$r_resched = $make('Scenario: Reschedule (change request)', 15);
$notify->notifyReservationSubmitted($r_resched);
// Create change request to move schedule date +2 days
$newDate = $r_resched->schedule_date->copy()->addDays(2);
$change = ReservationChange::create([
    'reservation_id' => $r_resched->reservation_id,
    'requested_by' => $requestor->id,
    'changes_requested' => [
        'schedule_date' => ['label'=>'Schedule','old'=>$r_resched->schedule_date->toDateTimeString(),'new'=>$newDate->toDateTimeString()],
    ],
    'requestor_notes' => 'Please reschedule due to conflict',
    'status' => 'pending',
    'requested_at' => now(),
]);
ReservationHistory::create(['reservation_id'=>$r_resched->reservation_id,'performed_by'=>$requestor->id,'action'=>'change_requested','remarks'=>'Reschedule +2 days','performed_at'=>now()]);
// Admin approves and applies
$r_resched->update(['schedule_date'=>$newDate]);
$change->update(['status'=>'approved','reviewed_by'=>$admin->id,'reviewed_at'=>now()]);
ReservationHistory::create(['reservation_id'=>$r_resched->reservation_id,'performed_by'=>$admin->id,'action'=>'changes_approved','remarks'=>'Reschedule approved','performed_at'=>now()]);
Notif::make([
    'user_id' => $requestor->id,
    'type' => Notif::TYPE_EDIT_APPROVED,
    'title' => 'Reservation Changes Approved',
    'message' => 'Your requested changes to reservation #'.$r_resched->reservation_id.' have been approved and applied.',
    'reservation_id' => $r_resched->reservation_id,
    'data' => [ 'action'=>'changes_approved', 'approved_by'=>$admin->first_name.' '.$admin->last_name ],
    'is_read' => false,
]);
$ids['reschedule'] = $r_resched->reservation_id;

out("Created scenarios:");
foreach ($ids as $k=>$v) {
    out(" - {$k}: #{$v}");
}

