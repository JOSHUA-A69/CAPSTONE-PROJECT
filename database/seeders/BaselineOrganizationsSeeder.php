<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;

class BaselineOrganizationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the sample Adviser user created by CreateRoleTestUsersSeeder
        $adviser = User::where('role', 'adviser')->where('email', 'cecilia.adviser@example.com')->first();

        $orgs = [
            ['org_name' => 'Himig Diwa Chorale', 'org_desc' => 'Leads musical worship during religious events.'],
            ['org_name' => 'Acolytes and Lectors', 'org_desc' => 'Serves at the altar and proclaims the Scriptures.'],
            ['org_name' => 'Children of Mary', 'org_desc' => 'Promotes Marian devotion through prayer and service.'],
            ['org_name' => 'Student Catholic Action', 'org_desc' => 'Fosters spiritual growth and social awareness among students.'],
            ['org_name' => 'Young Missionaries Club', 'org_desc' => 'Encourages missionary work and outreach participation.'],
            ['org_name' => 'Catechetical Organization', 'org_desc' => 'Supports religious education and catechism classes.'],
        ];

        foreach ($orgs as $row) {
            Organization::updateOrCreate(
                ['org_name' => $row['org_name']],
                [
                    'org_desc' => $row['org_desc'] ?? null,
                    'adviser_id' => $adviser?->id,
                ]
            );
        }
    }
}
