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
        // Add 'Cancellation Request' to notification type ENUM
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'Approval',
            'Reminder',
            'System Alert',
            'Priest Declined',
            'Assignment',
            'Update',
            'Urgent',
            'Cancellation Request'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous ENUM values
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'Approval',
            'Reminder',
            'System Alert',
            'Priest Declined',
            'Assignment',
            'Update',
            'Urgent'
        ) NULL");
    }
};
