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
        // Change the action column from ENUM to VARCHAR(255) to support more dynamic actions
        // and avoid "Data truncated" errors when adding new action types.
        if (Schema::hasTable('reservation_history')) {
            Schema::table('reservation_history', function (Blueprint $table) {
                $table->string('action', 100)->change(); 
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM if needed, but this is risky as we might have data that doesn't fit.
        // For now, we will just try to revert to a list inclusive of what we know, or just leave it as string.
        // Ideally we would list all known enums here.
        if (Schema::hasTable('reservation_history')) {
             DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'submitted',
                'adviser_notified',
                'adviser_approved',
                'advisor_rejected',
                'adviser_rejected',
                'admin_notified',
                'staff_followed_up',
                'priest_assigned',
                'priest_notified',
                'priest_confirmed',
                'priest_declined',
                'approved',
                'rejected',
                'cancelled',
                'updated',
                'cancellation_confirmed_by_staff',
                'cancellation_confirmed_by_adviser',
                'cancellation_confirmed_by_priest',
                'cancellation_rejected_by_adviser',
                'cancellation_rejected_by_priest',
                'cancellation_completed'
            ) NOT NULL");
        }
    }
};
