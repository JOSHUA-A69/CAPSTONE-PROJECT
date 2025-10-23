<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class BaselineServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'service_name' => 'Mass (Weekday/Sunday)',
                'service_category' => 'Liturgical Celebrations',
                'duration' => 60,
                'description' => 'Celebration of the Holy Mass. Duration may vary based on occasion.',
            ],
            [
                'service_name' => 'Recollection',
                'service_category' => 'Retreats and Recollections',
                'duration' => 240,
                'description' => 'Half-day spiritual recollection for students, staff, or organizations.',
            ],
            [
                'service_name' => 'Retreat',
                'service_category' => 'Retreats and Recollections',
                'duration' => 1440,
                'description' => 'Full-day or multi-day retreat; scheduling subject to venue and priest availability.',
            ],
            [
                'service_name' => 'Prayer Service',
                'service_category' => 'Prayer Services',
                'duration' => 45,
                'description' => 'Prayer and worship service including faith-sharing sessions.',
            ],
            [
                'service_name' => 'Outreach Activity',
                'service_category' => 'Outreach Activities',
                'duration' => 180,
                'description' => 'Community engagement/charity/outreach coordination activity.',
            ],
            [
                'service_name' => 'Daily Noon Mass',
                'service_category' => 'Daily Noon Mass',
                'duration' => 45,
                'description' => 'Public daily Noon Mass scheduling and publication.',
            ],
            [
                'service_name' => 'Bible Study / Catechesis',
                'service_category' => 'Catechetical Activities',
                'duration' => 90,
                'description' => 'Catechetical session or Bible study facilitated by CREaM.',
            ],
        ];

        foreach ($rows as $row) {
            Service::firstOrCreate(
                [
                    'service_name' => $row['service_name'],
                    'service_category' => $row['service_category'],
                ],
                [
                    'duration' => $row['duration'],
                    'description' => $row['description'],
                ]
            );
        }
    }
}
