<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationBookingRequest as OBR;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OrganizationBookingRequestsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Organization::query()->inRandomOrder()->take(5)->get();
        if ($orgs->isEmpty()) {
            $this->command?->warn('No organizations found; skipping OrganizationBookingRequestsDemoSeeder');
            return;
        }

        // Pick some requestors (non-admin) and advisers
        $requestors = User::whereNotIn('role', ['admin','staff','adviser','priest'])->take(10)->get();
        if ($requestors->isEmpty()) {
            // fallback: allow any users
            $requestors = User::inRandomOrder()->take(10)->get();
        }

        $statuses = ['pending','approved','rejected'];
        $activityPool = [
            'Community Outreach', 'Fundraising Event', 'Youth Fellowship', 'Choir Practice',
            'Seminar', 'Retreat', 'Workshop', 'Volunteer Drive', 'Health Mission', 'Education Fair'
        ];

        $count = 0;
        foreach (range(0, 5) as $m) { // last 6 months
            $monthStart = now()->startOfMonth()->subMonths($m);
            $monthEnd = $monthStart->copy()->endOfMonth();

            foreach ($orgs as $org) {
                foreach (range(1, 6) as $i) { // 6 per org per month
                    $status = Arr::random($statuses);
                    $submitted = $monthStart->copy()->addDays(rand(0, 20))->addHours(rand(0, 20));
                    $requestedDate = $monthStart->copy()->addDays(rand(5, 25))->addHours(rand(8, 15));

                    $data = [
                        'requestor_id' => optional($requestors->random())->id,
                        'organization_id' => $org->org_id,
                        'activity_name' => Arr::random($activityPool),
                        'purpose' => Str::random(30),
                        'requested_date' => $requestedDate,
                        'requested_venue' => 'Main Hall',
                        'estimated_participants' => rand(20, 200),
                        'special_requirements' => rand(0,1) ? 'Projector, chairs' : null,
                        'status' => $status,
                        'submitted_at' => $submitted,
                        'adviser_notified_at' => $submitted->copy()->addHours(rand(1, 12)),
                    ];

                    if ($status === 'approved') {
                        $data['approved_by'] = $org->adviser_id;
                        $data['adviser_comments'] = 'Approved. Coordinate with staff.';
                        $data['adviser_responded_at'] = $submitted->copy()->addHours(rand(6, 48));
                    } elseif ($status === 'rejected') {
                        $data['rejected_by'] = $org->adviser_id;
                        $data['rejection_reason'] = 'Schedule conflict or insufficient details.';
                        $data['adviser_responded_at'] = $submitted->copy()->addHours(rand(6, 48));
                    }

                    OBR::create($data);
                    $count++;
                }
            }
        }

        $this->command?->info("✅ Created {$count} demo organization booking requests.");
    }
}
