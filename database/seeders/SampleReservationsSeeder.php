<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Organization;
use App\Models\Service;
use App\Models\Venue;
use Carbon\Carbon;

class SampleReservationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required data
        $users = User::whereIn('role', ['student', 'staff', 'adviser'])->get();
        $organizations = Organization::limit(4)->get();
        $services = Service::limit(5)->get();
        $venues = Venue::limit(3)->get();

        if ($users->isEmpty() || $services->isEmpty() || $venues->isEmpty()) {
            $this->command->warn('Missing required data. Make sure to run BaselineServicesSeeder, BaselineVenuesSeeder, and user seeders first.');
            return;
        }

        // Create sample reservations for the next few weeks
        $sampleReservations = [
            [
                'activity_name' => 'Sunday Morning Mass',
                'purpose' => 'Weekly Sunday celebration for the university community',
                'schedule_date' => Carbon::now()->addDays(3)->setTime(9, 0, 0),
                'status' => 'approved',
                'participants_count' => 200,
                'theme' => 'Faith and Community',
            ],
            [
                'activity_name' => 'Student Retreat Day',
                'purpose' => 'Spiritual retreat for college students',
                'schedule_date' => Carbon::now()->addWeek()->setTime(8, 0, 0),
                'status' => 'approved',
                'participants_count' => 50,
                'theme' => 'Finding Peace in Prayer',
            ],
            [
                'activity_name' => 'Organization Recollection',
                'purpose' => 'Half-day recollection for student organization members',
                'schedule_date' => Carbon::now()->addDays(10)->setTime(14, 0, 0),
                'status' => 'pending',
                'participants_count' => 30,
                'theme' => 'Service and Sacrifice',
            ],
            [
                'activity_name' => 'Faculty Mass',
                'purpose' => 'Monthly mass for faculty and staff',
                'schedule_date' => Carbon::now()->addDays(5)->setTime(12, 0, 0),
                'status' => 'approved',
                'participants_count' => 75,
                'theme' => 'Education as Mission',
            ],
            [
                'activity_name' => 'Wedding Ceremony',
                'purpose' => 'Wedding celebration for alumni couple',
                'schedule_date' => Carbon::now()->addWeeks(2)->setTime(16, 0, 0),
                'status' => 'adviser_approved',
                'participants_count' => 150,
                'theme' => 'Love and Commitment',
            ],
            [
                'activity_name' => 'Memorial Service',
                'purpose' => 'Memorial service for departed faculty member',
                'schedule_date' => Carbon::now()->addDays(7)->setTime(10, 0, 0),
                'status' => 'approved',
                'participants_count' => 100,
                'theme' => 'Remembrance and Hope',
            ],
            [
                'activity_name' => 'Christmas Novena Day 1',
                'purpose' => 'First day of Christmas novena celebration',
                'schedule_date' => Carbon::now()->addDays(15)->setTime(18, 0, 0),
                'status' => 'approved',
                'participants_count' => 300,
                'theme' => 'Preparing for Christ',
            ],
            [
                'activity_name' => 'Youth Fellowship Gathering',
                'purpose' => 'Monthly youth fellowship and prayer meeting',
                'schedule_date' => Carbon::now()->addDays(12)->setTime(19, 0, 0),
                'status' => 'pending',
                'participants_count' => 40,
                'theme' => 'Young Hearts for Christ',
            ]
        ];

        foreach ($sampleReservations as $index => $reservationData) {
            // Rotate through available data
            $user = $users->get($index % $users->count());
            $org = $organizations->get($index % $organizations->count());
            $service = $services->get($index % $services->count());
            $venue = $venues->get($index % $venues->count());

            // Check if reservation already exists to avoid duplicates
            $existing = Reservation::where('activity_name', $reservationData['activity_name'])
                ->where('schedule_date', $reservationData['schedule_date'])
                ->first();

            if (!$existing) {
                Reservation::create([
                    'user_id' => $user->id,
                    'org_id' => $org ? $org->org_id : null,
                    'venue_id' => $venue->venue_id,
                    'service_id' => $service->service_id,
                    'activity_name' => $reservationData['activity_name'],
                    'purpose' => $reservationData['purpose'],
                    'schedule_date' => $reservationData['schedule_date'],
                    'status' => $reservationData['status'],
                    'participants_count' => $reservationData['participants_count'],
                    'theme' => $reservationData['theme'],
                    'details' => 'Sample reservation created for testing calendar display functionality.',
                    'priest_selection_type' => 'specific',
                ]);

                $this->command->info("Created reservation: {$reservationData['activity_name']} on {$reservationData['schedule_date']->format('Y-m-d H:i')}");
            } else {
                $this->command->warn("Skipped duplicate reservation: {$reservationData['activity_name']}");
            }
        }

        $this->command->info('Sample reservations seeded successfully!');
    }
}
