<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;

class BaselineOrganizationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This seeder is idempotent and will also restore organizations that were soft-deleted.
     */
    public function run(): void
    {
        // Prefer a specifically created adviser; fall back to any adviser if the sample user is missing
        $adviser = User::where('role', 'adviser')
            ->where('email', 'cecilia.adviser@example.com')
            ->first()
            ?? User::where('role', 'adviser')->first();

        $orgs = [
            ['org_name' => 'Himig Diwa Chorale', 'org_desc' => 'Leads musical worship during religious events.'],
            ['org_name' => 'Acolytes and Lectors', 'org_desc' => 'Serves at the altar and proclaims the Scriptures.'],
            ['org_name' => 'Children of Mary', 'org_desc' => 'Promotes Marian devotion through prayer and service.'],
            ['org_name' => 'Student Catholic Action', 'org_desc' => 'Fosters spiritual growth and social awareness among students.'],
            ['org_name' => 'Young Missionaries Club', 'org_desc' => 'Encourages missionary work and outreach participation.'],
            ['org_name' => 'Catechetical Organization', 'org_desc' => 'Supports religious education and catechism classes.'],
        ];

        foreach ($orgs as $data) {
            // Include trashed records so we can revive them instead of duplicating
            $existing = Organization::withTrashed()->where('org_name', $data['org_name'])->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'org_desc' => $data['org_desc'],
                    'adviser_id' => $adviser?->id,
                ]);
            } else {
                Organization::create([
                    'org_name' => $data['org_name'],
                    'org_desc' => $data['org_desc'],
                    'adviser_id' => $adviser?->id,
                ]);
            }
        }
    }
}
