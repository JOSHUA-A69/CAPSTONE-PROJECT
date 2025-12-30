<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existing = User::where('email', 'me@example.com')->first();
        if (!$existing) {
            User::create([
                'first_name' => 'Admin',
                'middle_name' => null,
                'last_name' => 'User',
                'phone' => '+63-912-000-0999',
                'role' => 'admin',
                'email' => 'me@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
        }
    }
}
