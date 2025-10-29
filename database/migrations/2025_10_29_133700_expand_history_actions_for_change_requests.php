<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'updated',
                'submitted',
                'approved',
                'adviser_notified',
                'adviser_approved',
                'adviser_rejected',
                'admin_notified',
                'admin_approved',
                'rejected',
                'cancelled',
                'contacted_requestor',
                'requestor_confirmed',
                'confirmed_by_requestor',
                'declined_by_requestor',
                'approved_by_staff',
                'priest_assigned',
                'priest_notified',
                'priest_confirmed',
                'priest_declined',
                'priest_cancelled_confirmation',
                'priest_reassigned',
                'staff_followed_up',
                'status_updated',
                'cancellation_requested',
                'cancellation_confirmed_by_staff',
                'cancellation_confirmed_by_admin',
                'cancellation_confirmed_by_adviser',
                'cancellation_confirmed_by_priest',
                'cancellation_completed',
                'completed',
                'marked_not_available',
                'change_requested',
                'changes_approved',
                'changes_rejected'
            ) NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            // Revert to a previous broad set that existed earlier (without the newly added change_* and marked_not_available etc.)
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'updated',
                'submitted',
                'approved',
                'adviser_approved',
                'admin_approved',
                'rejected',
                'cancelled',
                'contacted_requestor',
                'requestor_confirmed',
                'approved_by_staff',
                'priest_confirmed',
                'priest_declined',
                'priest_cancelled_confirmation',
                'priest_reassigned',
                'staff_followed_up',
                'status_updated',
                'cancellation_requested',
                'cancellation_confirmed_by_staff',
                'cancellation_confirmed_by_admin',
                'cancellation_confirmed_by_adviser',
                'cancellation_confirmed_by_priest',
                'cancellation_completed'
            ) NOT NULL");
        }
    }
};
