<?php

namespace Tests\Feature;

use App\Models\LiturgicalSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffCalendarUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_update_schedule_date_and_public_flag(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff);

        // Create initial schedule (public and on a given date)
        $schedule = LiturgicalSchedule::factory()->create([
            'created_by' => $staff->id,
            'schedule_date' => now()->addDays(3)->toDateString(),
            'is_public' => true,
        ]);

        // Update payload: move date by +2, and set private
        $newDate = now()->addDays(5)->toDateString();
        $response = $this->put(route('staff.calendar.update', $schedule->schedule_id), [
            'title' => 'Test Schedule',
            'description' => 'Updated description',
            'schedule_date' => $newDate,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'location' => null,
            'venue_id' => null,
            'priest_id' => null,
            'external_priest_name' => null,
            'external_priest_contact' => null,
            'event_type' => 'institutional_mass',
            'mass_subtype' => null,
            'is_public' => 0,
        ]);

        $response->assertRedirect(route('staff.calendar.index'));

        $schedule->refresh();
        $this->assertSame($newDate, $schedule->schedule_date->toDateString(), 'Schedule date should be updated');
        $this->assertFalse($schedule->is_public, 'Schedule should be private after update');
    }
}
