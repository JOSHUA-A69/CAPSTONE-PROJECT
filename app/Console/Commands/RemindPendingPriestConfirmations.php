<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Notification;
use App\Services\ReservationNotificationService;
use Illuminate\Console\Command;

class RemindPendingPriestConfirmations extends Command
{
    /** @var string */
    protected $signature = 'reservations:remind-priest-confirmations
                            {--remind-hours=24 : Hours since notification to remind priest}
                            {--escalate-hours=48 : Hours since notification to escalate to admin/staff}';

    /** @var string */
    protected $description = 'Send reminders for reservations pending priest confirmation, and escalate older ones to Admin/Staff';

    public function __construct(private ReservationNotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $remindHours = (int) $this->option('remind-hours');
        $escalateHours = (int) $this->option('escalate-hours');

        $now = now();
        $this->info("Checking pending priest confirmations (remind >= {$remindHours}h, escalate >= {$escalateHours}h)...");

        $pending = Reservation::with(['officiant','service','user'])
            ->where('status', 'pending_priest_confirmation')
            ->where('priest_confirmation', 'pending')
            ->whereNotNull('priest_notified_at')
            ->get();

        $reminded = 0; $escalated = 0;
        foreach ($pending as $r) {
            $hours = $r->priest_notified_at->diffInHours($now);

            if ($hours >= $escalateHours) {
                // Escalate to Admin/Staff
                try {
                    $admins = User::whereIn('role', ['admin','staff'])->get();
                    $msg = "Priest has not responded for reservation #{$r->reservation_id} ({$r->service->service_name}). Please review.";
                    foreach ($admins as $admin) {
                        Notification::create([
                            'user_id' => $admin->id,
                            'reservation_id' => $r->reservation_id,
                            'message' => $msg,
                            'type' => 'Reminder',
                            'sent_at' => now(),
                            'data' => json_encode([
                                'action' => 'priest_confirmation_escalation',
                                'hours' => $hours,
                            ]),
                        ]);
                    }
                    // History
                    $r->history()->create([
                        'performed_by' => null,
                        'action' => 'status_updated',
                        'remarks' => 'Automated escalation: priest not responded for '.$hours.'h',
                        'performed_at' => now(),
                    ]);
                    $escalated++;
                } catch (\Exception $e) {
                    $this->error('Escalation failed for reservation #'.$r->reservation_id.': '.$e->getMessage());
                }
                continue;
            }

            if ($hours >= $remindHours) {
                // Remind priest
                try {
                    $priest = $r->officiant;
                    if ($priest) {
                        Notification::create([
                            'user_id' => $priest->id,
                            'reservation_id' => $r->reservation_id,
                            'message' => 'Reminder: Please confirm your assignment for '.$r->service->service_name.'.',
                            'type' => 'Reminder',
                            'sent_at' => now(),
                            'data' => json_encode([
                                'action' => 'priest_confirmation_reminder',
                            ]),
                        ]);
                        // History
                        $r->history()->create([
                            'performed_by' => null,
                            'action' => 'status_updated',
                            'remarks' => 'Automated priest confirmation reminder sent ('.$hours.'h)',
                            'performed_at' => now(),
                        ]);
                        $reminded++;
                    }
                } catch (\Exception $e) {
                    $this->error('Reminder failed for reservation #'.$r->reservation_id.': '.$e->getMessage());
                }
            }
        }

        $this->info("Reminders sent: {$reminded}; Escalations sent: {$escalated}");
        return self::SUCCESS;
    }
}
