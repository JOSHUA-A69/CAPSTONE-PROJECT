<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Reservation;
use App\Models\ReservationHistory;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Venue;

class ReportDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Non-destructive guard: if we already have demo data, skip
        $existingDemo = Reservation::where('purpose', 'like', 'Demo:%')->count();
        if ($existingDemo >= 50) {
            $this->command?->warn("Demo data already present ({$existingDemo}). Skipping.");
            return;
        }

        $requestor = User::where('role','requestor')->where('status','active')->first();
        $adviser = User::where('role','adviser')->where('status','active')->first();
        $admin = User::whereIn('role',['admin','staff'])->where('status','active')->first();
        $orgs = Organization::orderBy('org_name')->get();
        $services = Service::orderBy('service_name')->get();
        $venues = Venue::orderBy('name')->get();
        $priests = User::where('role','priest')->where('status','active')->get();

        if (!$requestor || !$adviser || !$admin || $orgs->isEmpty() || $services->isEmpty()) {
            $this->command?->error('Missing baseline seed data (users/orgs/services). Run baseline seeders first.');
            return;
        }
        if ($priests->isEmpty()) {
            $this->command?->error('No priest accounts found. Run PriestSeeder first.');
            return;
        }

        $statuses = [
            'pending',
            'adviser_approved',
            'rejected',
            'admin_approved',
            'pending_priest_confirmation',
            'approved', // final
            'cancelled',
            'pending_priest_reassignment',
        ];

        $now = now();
        $created = 0;

        // Generate across last ~120 days and next ~30 days
        foreach ($orgs->take(4) as $org) {
            foreach ($services->take(6) as $service) {
                // 8 reservations per org-service with varied statuses
                foreach ($statuses as $idx => $status) {
                    $daysOffset = rand(-120, 30);
                    $scheduleDate = $now->copy()->addDays($daysOffset)->setTime(rand(8,16), [0,30][rand(0,1)]);
                    $venue = $venues->random() ?? null;
                    $priest = $priests->random();

                    $r = Reservation::create([
                        'user_id' => $requestor->id,
                        'org_id' => $org->org_id,
                        'venue_id' => optional($venue)->venue_id,
                        'service_id' => $service->service_id,
                        'schedule_date' => $scheduleDate,
                        'status' => 'pending', // start pending; we transition below
                        'purpose' => 'Demo: '.$org->org_name.' / '.$service->service_name.' (#'.Str::random(6).')',
                        'details' => 'Demo reservation for reports coverage',
                    ]);

                    // History: created
                    ReservationHistory::create([
                        'reservation_id' => $r->reservation_id,
                        'performed_by' => $requestor->id,
                        'action' => 'created',
                        'remarks' => 'Demo created',
                        'performed_at' => $now,
                    ]);

                    // Transition through flows to reach target status
                    switch ($status) {
                        case 'pending':
                            // leave as pending
                            break;
                        case 'adviser_approved':
                            $r->update([
                                'adviser_notified_at' => $now,
                                'adviser_responded_at' => $now,
                                'status' => 'adviser_approved',
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $adviser->id,
                                'action' => 'adviser_approved',
                                'remarks' => 'Demo adviser approved',
                                'performed_at' => $now,
                            ]);
                            break;
                        case 'rejected':
                            $r->update([
                                'adviser_notified_at' => $now,
                                'adviser_responded_at' => $now,
                                'status' => 'rejected',
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $adviser->id,
                                'action' => 'adviser_rejected',
                                'remarks' => 'Demo adviser rejected',
                                'performed_at' => $now,
                            ]);
                            break;
                        case 'admin_approved':
                            // assign priest, awaiting confirmation
                            $r->update([
                                'adviser_notified_at' => $now,
                                'adviser_responded_at' => $now,
                                'status' => 'admin_approved',
                                'officiant_id' => $priest->id,
                                'priest_notified_at' => $now,
                                'priest_confirmation' => 'pending',
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $admin->id,
                                'action' => 'priest_assigned',
                                'remarks' => 'Demo priest assigned',
                                'performed_at' => $now,
                            ]);
                            break;
                        case 'pending_priest_confirmation':
                            $r->update([
                                'adviser_notified_at' => $now,
                                'adviser_responded_at' => $now,
                                'status' => 'pending_priest_confirmation',
                                'officiant_id' => $priest->id,
                                'priest_notified_at' => $now,
                                'priest_confirmation' => 'pending',
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $admin->id,
                                'action' => 'priest_assigned',
                                'remarks' => 'Demo awaiting priest confirmation',
                                'performed_at' => $now,
                            ]);
                            break;
                        case 'approved':
                            // go through full approval
                            $r->update([
                                'adviser_notified_at' => $now,
                                'adviser_responded_at' => $now,
                                'status' => 'admin_approved',
                                'officiant_id' => $priest->id,
                                'priest_notified_at' => $now,
                                'priest_confirmation' => 'pending',
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $admin->id,
                                'action' => 'priest_assigned',
                                'remarks' => 'Demo priest assigned',
                                'performed_at' => $now,
                            ]);
                            // priest confirms
                            $r->update([
                                'priest_confirmation' => 'confirmed',
                                'priest_confirmed_at' => $now,
                                'status' => 'approved',
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $priest->id,
                                'action' => 'priest_confirmed',
                                'remarks' => 'Demo confirmed',
                                'performed_at' => $now,
                            ]);
                            break;
                        case 'cancelled':
                            $r->update([
                                'status' => 'cancelled',
                                'cancellation_reason' => 'Demo cancellation',
                                'cancelled_by' => $admin->id,
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $admin->id,
                                'action' => 'cancelled',
                                'remarks' => 'Demo cancelled',
                                'performed_at' => $now,
                            ]);
                            break;
                        case 'pending_priest_reassignment':
                            // simulate decline then reassignment pending
                            $r->update([
                                'status' => 'pending_priest_reassignment',
                                'officiant_id' => null,
                                'priest_confirmation' => 'declined',
                                'priest_confirmed_at' => $now,
                            ]);
                            ReservationHistory::create([
                                'reservation_id' => $r->reservation_id,
                                'performed_by' => $priest->id,
                                'action' => 'priest_declined',
                                'remarks' => 'Demo priest declined',
                                'performed_at' => $now,
                            ]);
                            break;
                    }

                    $created++;
                }
            }
        }

        $this->command?->info("✅ Created {$created} demo reservations across orgs/services/statuses.");
        $this->command?->info('Use these to test Quarterly, Statistics, Activities, Booking Summary, and Adviser-only views.');
    }
}
