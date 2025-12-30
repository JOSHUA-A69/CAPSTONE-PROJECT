<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only apply MySQL-specific ENUM modifications on MySQL; SQLite will treat these columns as TEXT
        if (DB::getDriverName() === 'mysql') {
            // Expand the status enum to support adviser/admin flow
            DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'adviser_approved', 'admin_approved', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");

            // Expand history action enum
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM('created', 'updated', 'approved', 'adviser_approved', 'admin_approved', 'rejected', 'cancelled')");
        }
    }

    public function down(): void
    {
        // Only apply MySQL-specific ENUM modifications on MySQL
        if (DB::getDriverName() === 'mysql') {
            // Revert to original values
            DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'approved', 'cancelled') DEFAULT 'pending'");
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM('created', 'updated', 'approved', 'cancelled')");
        }
    }
};
