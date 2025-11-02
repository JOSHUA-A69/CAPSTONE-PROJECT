<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PriestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priests = [
            [
                'first_name' => 'Fr. James',
                'last_name' => '',
                'email' => 'james@gmail.com',
                'password' => 'james1234',
                'role' => 'priest',
            ],
            [
                'first_name' => 'Fr. Jep',
                'last_name' => '',
                'email' => 'jep@gmail.com',
                'password' => 'jep1234',
                'role' => 'priest',
            ],
            [
                'first_name' => 'Fr. Aldrin',
                'last_name' => '',
                'email' => 'aldrin@gmail.com',
                'password' => 'aldrin1234',
                'role' => 'priest',
            ],
        ];

        foreach ($priests as $priestData) {
            User::updateOrCreate(
                ['email' => $priestData['email']], // Find by email
                [
                    'first_name' => $priestData['first_name'],
                    'last_name' => $priestData['last_name'],
                    'password' => Hash::make($priestData['password']),
                    'role' => $priestData['role'],
                    'email_verified_at' => now(),
                    // Our schema uses a single 'status' column to denote account state
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('✅ 3 Priest users created successfully!');
        $this->command->info('   - james@gmail.com (password: james1234) - Fr. James');
        $this->command->info('   - jep@gmail.com (password: jep1234) - Fr. Jep');
        $this->command->info('   - aldrin@gmail.com (password: aldrin1234) - Fr. Aldrin');
    }
}
