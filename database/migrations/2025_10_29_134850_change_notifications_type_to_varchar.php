<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'type')) {
            // Switch from ENUM to VARCHAR to avoid future enum drift and truncation warnings
            DB::statement("ALTER TABLE `notifications` MODIFY `type` VARCHAR(100) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications') && Schema::hasColumn('notifications', 'type')) {
            // Revert to the last known enum set used in prior migrations
            DB::statement(
                "ALTER TABLE `notifications` MODIFY `type` ENUM('Approval','Reminder','System Alert','Priest Declined','Assignment','Update') NULL"
            );
        }
    }
};
