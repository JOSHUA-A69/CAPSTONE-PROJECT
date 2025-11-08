<?php

namespace Database\Factories;

use App\Models\LiturgicalSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class LiturgicalScheduleFactory extends Factory
{
    protected $model = LiturgicalSchedule::class;

    public function definition(): array
    {
        return [
            'title' => 'Test Schedule',
            'description' => $this->faker->sentence(),
            'schedule_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'location' => null,
            'venue_id' => null,
            'priest_id' => null,
            'external_priest_name' => null,
            'external_priest_contact' => null,
            'event_type' => 'institutional_mass',
            'mass_subtype' => null,
            'is_public' => true,
            'created_by' => 0, // override in tests after creating user
        ];
    }
}
