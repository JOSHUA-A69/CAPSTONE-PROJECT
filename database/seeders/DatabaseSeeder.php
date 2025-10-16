<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed role test users (admin, staff, adviser, priest, requestor)
        $this->call(CreateRoleTestUsersSeeder::class);

        // Seed baseline services catalog aligned with thesis scope
        $this->call(BaselineServicesSeeder::class);

    // Seed student organizations and assign the Adviser user
    $this->call(BaselineOrganizationsSeeder::class);

    // Seed common venues for scheduling
    $this->call(BaselineVenuesSeeder::class);
    }
}
