<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateRoleTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin' => ['first_name' => 'Alice', 'middle_name' => 'M', 'last_name' => 'Admin', 'email' => 'alice.admin@example.com', 'phone' => '+63-912-000-0000'],
            'staff' => ['first_name' => 'Bob', 'middle_name' => null, 'last_name' => 'Staff', 'email' => 'bob.staff@example.com', 'phone' => '+63-912-000-0001'],
            'adviser' => ['first_name' => 'Cecilia', 'middle_name' => 'L', 'last_name' => 'Adviser', 'email' => 'cecilia.adviser@example.com', 'phone' => '+63-912-000-0002'],
            'priest' => ['first_name' => 'Daniel', 'middle_name' => null, 'last_name' => 'Priest', 'email' => 'daniel.priest@example.com', 'phone' => '+63-912-000-0003'],
            'requestor' => ['first_name' => 'Eve', 'middle_name' => 'R', 'last_name' => 'Requestor', 'email' => 'eve.requestor@example.com', 'phone' => '+63-912-000-0004'],
        ];

        foreach ($roles as $role => $data) {
            User::factory()->create([
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role' => $role,
                'password' => Hash::make('password'),
            ]);
        }
    }
}
