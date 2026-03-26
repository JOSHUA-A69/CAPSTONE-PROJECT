<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\OrganizationBookingRequest;
use App\Models\Organization;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;

class MarchScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting March Scenario Seeder...');

        // 1. Ensure Dependencies Exist
        
        // Venue
        $venue = Venue::firstOrCreate(
            ['name' => 'Main Parish Church'],
            ['location' => 'Town Center', 'capacity' => 300]
        );

        // Services
        if (Service::count() === 0) {
            $this->command->info('Creating default services...');
            // Use standard categories to avoid Enum constraint issues
            Service::create(['service_name' => 'Mass', 'service_category' => 'Liturgical Celebrations', 'description' => 'Holy Mass']);
            Service::create(['service_name' => 'Baptism', 'service_category' => 'Liturgical Celebrations', 'description' => 'Holy Baptism']);
            Service::create(['service_name' => 'Wedding', 'service_category' => 'Liturgical Celebrations', 'description' => 'Holy Matrimony']);
        }
        $services = Service::all();

        // Requestors
        $requestors = User::where('role', 'requestor')->get();
        if ($requestors->isEmpty()) {
            $this->command->info('Creating test requestors...');
            $requestors = User::factory()->count(5)->create(['role' => 'requestor']);
        }

        // Adviser
        $adviser = User::where('role', 'adviser')->first();
        if (!$adviser) {
            $this->command->info('Creating test adviser...');
            $adviser = User::factory()->create(['role' => 'adviser']);
        }

        // 2. Ensure Specific Organizations Exist
        $himigOrg = Organization::firstOrCreate(
            ['org_name' => 'Himig Diwa Chorale'],
            ['org_desc' => 'Church Choir Group', 'adviser_id' => $adviser->id]
        );

        $acolyteOrg = Organization::firstOrCreate(
            ['org_name' => 'Acolytes and Lectors'],
            ['org_desc' => 'Church Servers and Readers', 'adviser_id' => $adviser->id]
        );

        // 3. Create 10 Reservations for March
        $this->command->info('Seeding 10 Reservations for March 2026...');

        for ($i = 0; $i < 10; $i++) {
            // Select Org strategy:
            // 0-3: None (40%)
            // 4-6: Himig (30%)
            // 7-9: Acolyte (30%)
            $rand = rand(0, 9);
            $selectedOrgId = null;
            
            if ($rand >= 4 && $rand <= 6) {
                // Check if org exists before accessing property
                if ($himigOrg) {
                    $selectedOrgId = $himigOrg->org_id;
                }
            } elseif ($rand >= 7) {
                // Check if org exists before accessing property
                if ($acolyteOrg) {
                    $selectedOrgId = $acolyteOrg->org_id;
                }
            }

            // Random date in March 2026
            $date = Carbon::create(2026, 3, rand(1, 31), rand(7, 18), 0, 0);

            // Ensure we have user/service/venue before creating
            if ($requestors->count() > 0 && $services->count() > 0 && $venue) {
                Reservation::create([
                    'user_id' => $requestors->random()->id,
                    'service_id' => $services->random()->service_id,
                    'venue_id' => $venue->venue_id,
                    'schedule_date' => $date,
                    'status' => 'pending', 
                    'priest_selection_type' => 'any_available',
                    'org_id' => $selectedOrgId,
                    'purpose' => 'March Special Event ' . ($i + 1),
                    'participants_count' => rand(50, 150),
                ]);
            }
        }
        $this->command->info('10 Reservations created.');

        // 4. Create 10 Organization Bookings (5 Himig, 5 Acolytes)
        $this->command->info('Seeding 10 Organization Bookings for March 2026...');

        // Himig Bookings (5)
        for ($i = 0; $i < 5; $i++) {
            if ($himigOrg && $requestors->count() > 0) {
                OrganizationBookingRequest::create([
                    'requestor_id' => $requestors->random()->id, // Assuming a member requests it
                    'organization_id' => $himigOrg->org_id,
                    'activity_name' => 'Choral Practice Session ' . ($i + 1),
                    'purpose' => 'Rehearsal for Holy Week',
                    'requested_date' => Carbon::create(2026, 3, rand(1, 31), rand(16, 20), 0, 0),
                    'requested_venue' => 'Parish Hall',
                    'estimated_participants' => rand(15, 40),
                    'status' => 'pending',
                    'submitted_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        // Acolyte Bookings (5)
        for ($i = 0; $i < 5; $i++) {
            if ($acolyteOrg && $requestors->count() > 0) {
                OrganizationBookingRequest::create([
                    'requestor_id' => $requestors->random()->id,
                    'organization_id' => $acolyteOrg->org_id,
                    'activity_name' => 'Server Training ' . ($i + 1),
                    'purpose' => 'Training for new altar servers',
                    'requested_date' => Carbon::create(2026, 3, rand(1, 31), rand(8, 12), 0, 0),
                    'requested_venue' => 'Main Church',
                    'estimated_participants' => rand(10, 20),
                    'status' => 'pending',
                    'submitted_at' => now()->subDays(rand(1, 5)),
                ]);
            }
        }

        $this->command->info('10 Organization Bookings created.');
        $this->command->info('March Scenario Seeding Completed!');
    }
}
