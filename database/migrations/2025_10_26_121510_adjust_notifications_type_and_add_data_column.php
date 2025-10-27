<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Add data JSON column if missing
            Schema::table('notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('notifications', 'data')) {
                    $table->json('data')->nullable()->after('read_at');
                }
            });

            // Expand notifications.type enum to include values used across the app
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
                'Approval',
                'Reminder',
                'System Alert',
                'Update',
                'Assignment',
                'Action Required',
                'Priest Declined',
                'Urgent'
            ) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Shrink back enum; keep column to avoid data loss
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('Approval','Reminder','System Alert') NULL");
            // Optional: drop data column (commented out to preserve data)
            // Schema::table('notifications', function (Blueprint $table) {
            //     if (Schema::hasColumn('notifications', 'data')) {
            //         $table->dropColumn('data');
            //     }
            // });
        }
    }
};
