<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Organization;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\BaselineOrganizationsSeeder;
use Database\Seeders\BaselineServicesSeeder;
use Database\Seeders\BaselineVenuesSeeder;
use Database\Seeders\CreateRoleTestUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCore(): void
    {
        $this->seed(CreateRoleTestUsersSeeder::class);
        $this->seed(BaselineServicesSeeder::class);
        $this->seed(BaselineOrganizationsSeeder::class);
        $this->seed(BaselineVenuesSeeder::class);
    }

    public function test_happy_path_reservation_flow(): void
    {
        Mail::fake();
        $this->seedCore();

        $requestor = User::where('role','requestor')->first();
        $adviser = User::where('role','adviser')->first();
        $staff = User::where('role','staff')->first();
        $admin = User::where('role','admin')->first();
        $priest = User::where('role','priest')->first();

        $service = Service::first();
        $venue = Venue::first();
        $org = Organization::first();

        // Requestor submits reservation (14 days ahead)
        $this->actingAs($requestor)
            ->post(route('requestor.reservations.store'), [
                'service_id' => $service->service_id,
                'venue_id' => $venue->venue_id,
                'org_id' => $org->org_id,
                'preferred_officiant_id' => $priest->id,
                'schedule_date' => now()->addDays(14)->format('Y-m-d H:i'),
                'activity_name' => 'Retreat Orientation',
                'purpose' => 'Orientation before retreat',
                'participants_count' => 20,
            ])
            ->assertRedirect(route('requestor.reservations.index'));

        $reservation = Reservation::first();
        $this->assertNotNull($reservation);
        $this->assertSame('pending', $reservation->status);
        $this->assertDatabaseHas('reservation_history', [
            'reservation_id' => $reservation->reservation_id,
            'action' => 'submitted',
        ]);

        // Adviser approves
        $this->actingAs($adviser)
            ->post(route('adviser.reservations.approve', $reservation->reservation_id), [
                'remarks' => 'Approved by adviser for scheduling',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $reservation->refresh();
        $this->assertSame('adviser_approved', $reservation->status);
        $this->assertDatabaseHas('reservation_history', [
            'reservation_id' => $reservation->reservation_id,
            'action' => 'adviser_approved',
        ]);

        // Staff marks as contacted
        $this->actingAs($staff)
            ->post(route('staff.reservations.mark-contacted', $reservation->reservation_id))
            ->assertSessionHas('status','requestor-contacted');

        $reservation->refresh();
        $this->assertNotNull($reservation->contacted_at);
        $this->assertDatabaseHas('reservation_history', [
            'reservation_id' => $reservation->reservation_id,
            'action' => 'contacted_requestor',
        ]);

        // Admin assigns priest
        $this->actingAs($admin)
            ->post(route('admin.reservations.assign-priest', $reservation->reservation_id), [
                'officiant_id' => $priest->id,
            ])
            ->assertSessionHas('status','priest-assigned');

        $reservation->refresh();
        $this->assertSame('pending_priest_confirmation', $reservation->status);
        $this->assertSame($priest->id, $reservation->officiant_id);
        $this->assertDatabaseHas('reservation_history', [
            'reservation_id' => $reservation->reservation_id,
            'action' => 'priest_assigned',
        ]);

        // Priest confirms
        $this->actingAs($priest)
            ->post(route('priest.reservations.confirm', $reservation->reservation_id))
            ->assertSessionHas('status','reservation-confirmed');

        $reservation->refresh();
        $this->assertSame('approved', $reservation->status);
        $this->assertSame('confirmed', $reservation->priest_confirmation);
        $this->assertDatabaseHas('reservation_history', [
            'reservation_id' => $reservation->reservation_id,
            'action' => 'priest_confirmed',
        ]);

        // Notifications sanity
        $this->assertTrue(Notification::where('reservation_id', $reservation->reservation_id)->exists());
    }

    public function test_requestor_cancellation_policy(): void
    {
        Mail::fake();
        $this->seedCore();

        $requestor = User::where('role','requestor')->first();
        $service = Service::first();
        $venue = Venue::first();

        // >= 7 days allowed
        $this->actingAs($requestor)
            ->post(route('requestor.reservations.store'), [
                'service_id' => $service->service_id,
                'venue_id' => $venue->venue_id,
                'schedule_date' => now()->addDays(10)->format('Y-m-d H:i'),
                'activity_name' => 'Activity A',
            ]);
        $res1 = Reservation::latest()->first();

        $this->actingAs($requestor)
            ->post(route('requestor.reservations.cancel', $res1->reservation_id), [
                'reason' => 'We need to cancel.',
            ])
            ->assertSessionHas('status', 'cancellation-completed');
        $this->assertSame('cancelled', $res1->fresh()->status);
        $this->assertDatabaseHas('reservation_history', [
            'reservation_id' => $res1->reservation_id,
            'action' => 'cancelled',
        ]);

        // < 7 days blocked
        $this->actingAs($requestor)
            ->post(route('requestor.reservations.store'), [
                'service_id' => $service->service_id,
                'venue_id' => $venue->venue_id,
                'schedule_date' => now()->addDays(2)->format('Y-m-d H:i'),
                'activity_name' => 'Activity B',
            ]);
        $res2 = Reservation::latest()->first();

        $this->actingAs($requestor)
            ->post(route('requestor.reservations.cancel', $res2->reservation_id), [
                'reason' => 'Too soon to cancel?'
            ])
            ->assertSessionHas('error');
        $this->assertNotEquals('cancelled', $res2->fresh()->status);
    }
}
