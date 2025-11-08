<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class MassSubtypeServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Institutional Mass subtypes
        $institutional = [
            'University Opening Mass',
            'Thanksgiving Mass',
            'Convocation Mass',
            'Graduation Mass',
            'Feast Day Masses',
            'Memorial/Requiem Masses',
            'Special Celebration Masses',
        ];

        // Non-Institutional Mass subtypes
        $nonInstitutional = [
            'Daily Noon Mass',
            'Departmental or Group-requested Masses',
            'Recollection Masses',
            'Novenas and Devotions',
            'Prayer Services and Blessings',
            'Special Devotional Masses',
            'Taize Prayer Services',
            'Sacraments',
            'Community Outreach',
        ];

        foreach ($institutional as $name) {
            Service::firstOrCreate(
                [
                    'service_name' => $name,
                    'service_category' => 'Institutional Mass',
                ],
                [
                    'description' => $name . ' (auto-seeded subtype)',
                    'duration' => 60,
                ]
            );
        }

        foreach ($nonInstitutional as $name) {
            Service::firstOrCreate(
                [
                    'service_name' => $name,
                    'service_category' => 'Non-Institutional Mass',
                ],
                [
                    'description' => $name . ' (auto-seeded subtype)',
                    'duration' => 60,
                ]
            );
        }
    }
}
