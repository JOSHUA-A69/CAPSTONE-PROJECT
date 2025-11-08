<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Prefer schema change with DBAL; fallback to raw SQL for MySQL if DBAL not present
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->string('service_category', 100)->nullable()->change();
            });
        } catch (Throwable $e) {
            // Fallback: MySQL-specific alteration from ENUM to VARCHAR
            DB::statement('ALTER TABLE services MODIFY service_category VARCHAR(100) NULL');
        }
    }

    public function down(): void
    {
        // Revert to ENUM with the original application categories
        try {
            // Note: Changing back to ENUM may fail if data contains values outside the enum list.
            // Ensure values are normalized before running down migration.
            DB::statement("ALTER TABLE services MODIFY service_category ENUM(
                'Liturgical Celebrations',
                'Retreats and Recollections',
                'Prayer Services',
                'Outreach Activities',
                'Daily Noon Mass',
                'Catechetical Activities'
            ) NULL");
        } catch (Throwable $e) {
            // As a safe fallback, keep VARCHAR if reverting is unsafe.
        }
    }
};
