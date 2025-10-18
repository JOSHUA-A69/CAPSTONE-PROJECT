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
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'data')) {
                $table->json('data')->nullable()->after('type');
            }

            // Update type enum to include more notification types
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('Approval', 'Reminder', 'System Alert', 'Priest Declined', 'Assignment', 'Update') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'data')) {
                $table->dropColumn('data');
            }

            // Revert to original enum
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('Approval', 'Reminder', 'System Alert') NULL");
        });
    }
};
