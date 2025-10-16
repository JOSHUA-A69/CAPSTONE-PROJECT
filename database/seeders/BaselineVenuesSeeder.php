<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Venue;

class BaselineVenuesSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            ['name' => 'University Chapel', 'capacity' => 300, 'location' => 'Main Building'],
            ['name' => 'Auditorium', 'capacity' => 800, 'location' => 'Cultural Center'],
            ['name' => 'Prayer Room A', 'capacity' => 50, 'location' => 'Formation Center'],
        ];

        foreach ($venues as $v) {
            Venue::firstOrCreate(['name' => $v['name']], ['capacity' => $v['capacity'], 'location' => $v['location']]);
        }
    }
}
