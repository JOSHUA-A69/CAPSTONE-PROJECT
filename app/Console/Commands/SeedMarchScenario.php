<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedMarchScenario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:march-scenario';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed 10 Reservations and 10 Org Bookings for March 2026';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running custom seeder for March scenario...');
        $this->call('db:seed', [
            '--class' => 'Database\Seeders\MarchScenarioSeeder'
        ]);
        $this->info('March scenario seeded successfully.');
    }
}
