<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SystemSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role, array $overrides = []): User
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'role' => $role,
            'status' => 'active',
        ], $overrides));
        return $user;
    }

    public function test_dashboard_redirects_match_roles(): void
    {
        $roles = ['admin','staff','adviser','priest','requestor'];
        $routeMap = [
            'admin' => 'admin.dashboard',
            'staff' => 'staff.dashboard',
            'adviser' => 'adviser.dashboard',
            'priest' => 'priest.dashboard',
            'requestor' => 'requestor.dashboard',
        ];

        foreach ($roles as $role) {
            $user = $this->makeUser($role);
            $res = $this->actingAs($user)->get('/dashboard');
            $res->assertRedirect(route($routeMap[$role]));
        }
    }

    public function test_admin_core_pages_are_accessible(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get(route('admin.services.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.services.declined'))->assertOk();
        $this->actingAs($admin)->get(route('admin.services.calendar'))->assertOk();
        $this->actingAs($admin)->get(route('admin.notifications.index'))->assertOk();
    }

    public function test_staff_can_list_and_create_organizations(): void
    {
        $staff = $this->makeUser('staff');
        $adviser = $this->makeUser('adviser');

        // index page
        $this->actingAs($staff)->get(route('staff.organizations.index'))->assertOk();
        $this->actingAs($staff)->get(route('staff.organizations.create'))->assertOk();

        // create org via request rules (org_name Other + custom_org_name)
        $payload = [
            'adviser_id' => $adviser->id,
            'org_name' => 'Other',
            'custom_org_name' => 'QA Org '.Str::random(6),
            'org_desc' => 'Created by system smoke test',
        ];
        $this->actingAs($staff)
            ->post(route('staff.organizations.store'), $payload)
            ->assertRedirect(route('staff.organizations.index'));

        $this->assertDatabaseHas('organizations', [
            'adviser_id' => $adviser->id,
            'org_name' => $payload['custom_org_name'],
        ]);
    }

    public function test_admin_can_approve_user_accounts(): void
    {
        $admin = $this->makeUser('admin');
        $pendingUser = $this->makeUser('requestor', ['status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('admin.users.approve', $pendingUser->id))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_assign_priest_for_adviser_approved_reservation(): void
    {
        $admin = $this->makeUser('admin');
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');
        $priest = $this->makeUser('priest');

        // minimal service/venue/org
        $service = Service::create([
            'service_name' => 'Mass',
            'service_category' => 'Liturgical Celebrations',
            'description' => null,
            'duration' => 60,
        ]);
        $venue = Venue::create([
            'name' => 'Main Chapel',
            'capacity' => 100,
            'location' => 'Campus',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org',
            'org_desc' => null,
        ]);

        $reservation = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(3),
            'schedule_time' => '09:00:00',
            'status' => 'adviser_approved',
            'purpose' => 'Worship',
            'activity_name' => 'Sunday Mass',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.reservations.assign-priest', $reservation->reservation_id), [
                'officiant_id' => $priest->id,
            ])
            ->assertSessionHas('status', 'priest-assigned');

        $reservation->refresh();
        $this->assertEquals('admin_approved', $reservation->status);
        $this->assertEquals($priest->id, $reservation->officiant_id);
    }

    public function test_adviser_can_reject_reservation(): void
    {
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');

        $service = Service::create([
            'service_name' => 'Retreat',
            'service_category' => 'Retreats and Recollections',
            'description' => null,
            'duration' => 120,
        ]);
        $venue = Venue::create([
            'name' => 'Auditorium',
            'capacity' => 200,
            'location' => 'Main',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org Reject',
            'org_desc' => null,
        ]);

        $reservation = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(2)->setTime(10, 0),
            'status' => 'pending',
            'purpose' => 'Retreat',
            'activity_name' => 'Team Retreat',
        ]);

        $this->actingAs($adviser)
            ->post(route('adviser.reservations.reject', $reservation->reservation_id), [
                'reason' => 'Insufficient preparation time',
            ])
            ->assertSessionHas('status', 'reservation-rejected');

        $reservation->refresh();
        $this->assertEquals('rejected', $reservation->status);
    }

    public function test_staff_cannot_assign_priest_with_conflict(): void
    {
        $staff = $this->makeUser('staff');
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');
        $priest = $this->makeUser('priest');

        $service = Service::create([
            'service_name' => 'Mass',
            'service_category' => 'Liturgical Celebrations',
            'description' => null,
            'duration' => 60,
        ]);
        $venue = Venue::create([
            'name' => 'Chapel',
            'capacity' => 100,
            'location' => 'Campus',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org Conflict',
            'org_desc' => null,
        ]);

        $when = now()->addDays(4)->setTime(9, 0);
        $r1 = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => $when,
            'status' => 'adviser_approved',
            'purpose' => 'Worship',
            'activity_name' => 'Morning Mass',
        ]);
        $r2 = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => $when,
            'status' => 'adviser_approved',
            'purpose' => 'Worship',
            'activity_name' => 'Another Mass',
        ]);

        // Assign priest to first reservation
        $this->actingAs($staff)
            ->post(route('staff.reservations.assign-priest', $r1->reservation_id), [
                'officiant_id' => $priest->id,
            ])
            ->assertSessionHas('status', 'priest-assigned');

        // Attempt to assign same priest to conflicting reservation
        $this->actingAs($staff)
            ->post(route('staff.reservations.assign-priest', $r2->reservation_id), [
                'officiant_id' => $priest->id,
            ])
            ->assertSessionHas('error');

        $r2->refresh();
        $this->assertNull($r2->officiant_id);
    }

    public function test_requestor_reschedule_via_change_request_and_admin_approves(): void
    {
        $admin = $this->makeUser('admin');
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');
        $priest = $this->makeUser('priest');

        $service = Service::create([
            'service_name' => 'Prayer Service',
            'service_category' => 'Prayer Services',
            'description' => null,
            'duration' => 45,
        ]);
        $venue = Venue::create([
            'name' => 'Oratory',
            'capacity' => 50,
            'location' => 'North Wing',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org Change',
            'org_desc' => null,
        ]);

        $origDate = now()->addDays(6)->setTime(14, 0);
        $reservation = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'officiant_id' => $priest->id,
            'schedule_date' => $origDate,
            'status' => 'adviser_approved',
            'purpose' => 'Prayer',
            'activity_name' => 'Evening Prayer',
        ]);

        $newDate = now()->addDays(7)->format('Y-m-d');
        $newTime = '15:00';

        // Submit change request as requestor
        $this->actingAs($requestor)
            ->post(route('requestor.reservations.update', $reservation->reservation_id), [
                'service_id' => $service->service_id,
                'venue_id' => $venue->venue_id,
                'org_id' => $org->org_id,
                'officiant_id' => $priest->id,
                'schedule_date' => $newDate,
                'schedule_time' => $newTime,
                'activity_name' => $reservation->activity_name,
                'purpose' => $reservation->purpose,
                'details' => null,
                'notes' => 'Please move to a later time due to conflict.',
            ])
            ->assertSessionHas('status', 'change-request-submitted');

        // Find pending change request
        $change = \App\Models\ReservationChange::where('reservation_id', $reservation->reservation_id)
            ->where('status', 'pending')
            ->first();
        $this->assertNotNull($change);

        // Approve as admin
        $this->actingAs($admin)
            ->post(route('admin.change-requests.approve', $change->change_id))
            ->assertSessionHas('status');

        $reservation->refresh();
        $this->assertEquals($newDate . ' ' . $newTime . ':00', $reservation->schedule_date->format('Y-m-d H:i:s'));
    }

    public function test_priest_can_confirm_and_decline_assignment(): void
    {
        $staff = $this->makeUser('staff');
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');
        $priest = $this->makeUser('priest');

        $service = Service::create([
            'service_name' => 'Mass',
            'service_category' => 'Liturgical Celebrations',
            'description' => null,
            'duration' => 60,
        ]);
        $venue = Venue::create([
            'name' => 'Main Chapel',
            'capacity' => 100,
            'location' => 'Campus',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org Priest',
            'org_desc' => null,
        ]);

        $reservation = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(8)->setTime(8, 0),
            'status' => 'adviser_approved',
            'purpose' => 'Worship',
            'activity_name' => 'Sunday Mass',
        ]);

        // Staff assigns priest
        $this->actingAs($staff)
            ->post(route('staff.reservations.assign-priest', $reservation->reservation_id), [
                'officiant_id' => $priest->id,
            ])
            ->assertSessionHas('status', 'priest-assigned');

        $reservation->refresh();
        $this->assertEquals('pending_priest_confirmation', $reservation->status);

        // Priest confirms
        $this->actingAs($priest)
            ->post(route('priest.reservations.confirm', $reservation->reservation_id))
            ->assertSessionHas('status', 'reservation-confirmed');

        $reservation->refresh();
        $this->assertEquals('approved', $reservation->status);
        $this->assertEquals('confirmed', $reservation->priest_confirmation);

        // Priest declines a different assignment (unconfirmed)
        $reservation2 = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(9)->setTime(10, 0),
            'status' => 'pending_priest_confirmation',
            'officiant_id' => $priest->id,
            'purpose' => 'Worship',
            'activity_name' => 'Backup Mass',
        ]);

        $this->actingAs($priest)
            ->post(route('priest.reservations.decline', $reservation2->reservation_id), [
                'reason' => 'Schedule not feasible',
            ])
            ->assertSessionHas('status', 'reservation-declined');

        $reservation2->refresh();
        $this->assertEquals('pending_priest_reassignment', $reservation2->status);
        $this->assertNull($reservation2->officiant_id);
    }

    public function test_requestor_token_confirm_and_decline(): void
    {
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');

        $service = Service::create([
            'service_name' => 'Noon Mass',
            'service_category' => 'Daily Noon Mass',
            'description' => null,
            'duration' => 45,
        ]);
        $venue = Venue::create([
            'name' => 'Small Chapel',
            'capacity' => 40,
            'location' => 'South Wing',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org Tokens',
            'org_desc' => null,
        ]);

        $reservation = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(5)->setTime(12, 0),
            'status' => 'adviser_approved',
            'purpose' => 'Worship',
            'activity_name' => 'Noon Mass',
            'requestor_confirmation_token' => 'tok123',
            'contacted_at' => now(),
        ]);

        // Show confirmation page
        $this->actingAs($requestor)
            ->get(route('requestor.reservations.show-confirmation', [$reservation->reservation_id, 'tok123']))
            ->assertOk();

        // Confirm
        $this->actingAs($requestor)
            ->post(route('requestor.reservations.confirm-reservation', [$reservation->reservation_id, 'tok123']))
            ->assertSessionHas('status', 'reservation-confirmed');

        $reservation->refresh();
        $this->assertNotNull($reservation->requestor_confirmed_at);

        // Create another reservation to test decline path
        $reservation2 = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(6)->setTime(12, 0),
            'status' => 'adviser_approved',
            'purpose' => 'Worship',
            'activity_name' => 'Noon Mass 2',
            'requestor_confirmation_token' => 'tok456',
            'contacted_at' => now(),
        ]);

        $this->actingAs($requestor)
            ->post(route('requestor.reservations.decline-reservation', [$reservation2->reservation_id, 'tok456']))
            ->assertSessionHas('status', 'reservation-declined');

        $reservation2->refresh();
        $this->assertEquals('cancelled', $reservation2->status);
        $this->assertEquals('Declined by requestor after staff contact', $reservation2->cancellation_reason);
    }

    public function test_cancellation_flow_requestor_and_all_confirmers(): void
    {
        $requestor = $this->makeUser('requestor');
        $adviser = $this->makeUser('adviser');
        $staff = $this->makeUser('staff');
        $admin = $this->makeUser('admin');
        $priest = $this->makeUser('priest');

        $service = Service::create([
            'service_name' => 'Catechetical',
            'service_category' => 'Catechetical Activities',
            'description' => null,
            'duration' => 90,
        ]);
        $venue = Venue::create([
            'name' => 'Hall A',
            'capacity' => 80,
            'location' => 'East',
        ]);
        $org = Organization::create([
            'adviser_id' => $adviser->id,
            'org_name' => 'QA Org Cancel',
            'org_desc' => null,
        ]);

        $reservation = Reservation::create([
            'user_id' => $requestor->id,
            'org_id' => $org->org_id,
            'venue_id' => $venue->venue_id,
            'service_id' => $service->service_id,
            'schedule_date' => now()->addDays(10)->setTime(13, 0),
            'status' => 'approved',
            'purpose' => 'Teaching',
            'activity_name' => 'Catechism',
            'officiant_id' => $priest->id,
        ]);

        // Requestor initiates cancellation
        $this->actingAs($requestor)
            ->post(route('requestor.reservations.cancel', $reservation->reservation_id), [
                'reason' => 'Scheduling conflict due to exams',
            ])
            ->assertSessionHas('status', 'cancellation-requested');

        $cancel = \App\Models\ReservationCancellation::where('reservation_id', $reservation->reservation_id)
            ->latest('created_at')
            ->first();
        $this->assertNotNull($cancel);

        // Adviser confirms
        $this->actingAs($adviser)
            ->post(route('adviser.cancellations.confirm', $cancel->cancellation_id))
            ->assertSessionHas('success');

        // Priest confirms
        $this->actingAs($priest)
            ->post(route('priest.cancellations.confirm', $cancel->cancellation_id))
            ->assertSessionHas('success');

        // Staff confirms (completes cancellation per business rule: staff OR admin completes)
        $this->actingAs($staff)
            ->post(route('staff.cancellations.confirm', $cancel->cancellation_id))
            ->assertSessionHas('success');

        $cancel->refresh();
        $reservation->refresh();
        $this->assertEquals('completed', $cancel->status);
        $this->assertEquals('cancelled', $reservation->status);
    }
}
