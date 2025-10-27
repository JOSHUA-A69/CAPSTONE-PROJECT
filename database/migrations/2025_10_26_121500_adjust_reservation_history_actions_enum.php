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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Expand reservation_history.action to include all values used by controllers/services/observers
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'submitted',
                'adviser_notified',
                'adviser_approved',
                'adviser_rejected',
                'admin_notified',
                'staff_followed_up',
                'contacted_requestor',
                'approved_by_staff',
                'priest_assigned',
                'priest_reassigned',
                'priest_notified',
                'priest_confirmed',
                'priest_declined',
                'priest_cancelled_confirmation',
                'status_updated',
                'completed',
                'approved',
                'rejected',
                'cancelled',
                'updated'
            ) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Revert to a conservative smaller set (may fail if rows contain newer values)
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'updated',
                'approved',
                'adviser_approved',
                'admin_approved',
                'rejected',
                'cancelled'
            ) NOT NULL");
        }
    }
};
