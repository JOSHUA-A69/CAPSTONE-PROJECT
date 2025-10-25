<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUnacknowledgedCancellations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:check-cancellations {--since=24 : Hours back to include in the digest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a daily digest to staff/admin for recent cancellations to ensure follow-up and record-keeping';

    public function handle(): int
    {
        $hours = (int) $this->option('since');
        $since = now()->subHours($hours);

        $this->info("Scanning for cancellations since {$since->toDateTimeString()}...");

        // Find reservations cancelled within the time window
        $cancelled = Reservation::with(['user', 'service', 'venue'])
            ->where('status', 'cancelled')
            ->where('updated_at', '>=', $since)
            ->orderByDesc('updated_at')
            ->get();

        if ($cancelled->isEmpty()) {
            $this->info('No recent cancellations found.');
            return self::SUCCESS;
        }

        $this->warn("Found {$cancelled->count()} cancellation(s).");

        // Build a short summary
        $lines = $cancelled->map(function ($r) {
            $when = $r->updated_at->format('M d, Y h:i A');
            $svc = optional($r->service)->service_name ?? 'Service';
            $who = $r->user?->full_name ?? 'Unknown';
            return "#{$r->reservation_id} · {$svc} · {$r->schedule_date->format('M d, Y h:i A')} · by {$who} · cancelled at {$when}";
        })->toArray();

        $message = 'Recent cancellations (last ' . $hours . "h):<br>\n" . implode("<br>\n", array_map('e', $lines));

        // Notify all admins/staff in-app
        $recipients = User::whereIn('role', ['admin', 'staff'])->get();
        foreach ($recipients as $user) {
            Notification::create([
                'user_id' => $user->id,
                'reservation_id' => null,
                'message' => $message,
                'type' => 'Reminder',
                'sent_at' => now(),
                'data' => json_encode([
                    'action' => 'cancellations_digest',
                    'count' => $cancelled->count(),
                ]),
            ]);
        }

        $this->info('Digest notifications sent to admin/staff.');
        return self::SUCCESS;
    }
}
