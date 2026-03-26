<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip for SQLite as it doesn't support MODIFY
        if (config('database.default') === 'sqlite') {
            return;
        }
        
        // Change event_type from ENUM to VARCHAR to support new mass types
        DB::statement("ALTER TABLE liturgical_schedules MODIFY event_type VARCHAR(255) NOT NULL DEFAULT 'other'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM
        DB::statement("ALTER TABLE liturgical_schedules MODIFY event_type ENUM('mass','confession','adoration','retreat','seminar','meeting','celebration','other') NOT NULL DEFAULT 'other'");
    }
};
