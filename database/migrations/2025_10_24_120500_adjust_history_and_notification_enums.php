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
        // Only for MySQL
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        // Expand reservation_history.action enum to include all used actions
        DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
            'created',
            'submitted',
            'adviser_notified',
            'adviser_approved',
            'adviser_rejected',
            'admin_notified',
            'staff_followed_up',
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
            'approved',
            'rejected',
            'cancelled',
            'marked_not_available',
            'completed',
            'status_updated',
            'cancellation_requested',
            'cancellation_confirmed_by_staff',
            'cancellation_confirmed_by_admin',
            'cancellation_confirmed_by_adviser',
            'cancellation_confirmed_by_priest',
            'cancellation_completed'
        ) NOT NULL");

        // Expand notifications.type enum to include new types used by the app
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'Approval',
            'Reminder',
            'System Alert',
            'Priest Declined',
            'Assignment',
            'Update',
            'Urgent',
            'Action Required'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        // Revert notifications.type to previous expanded but without the new ones
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'Approval', 'Reminder', 'System Alert', 'Priest Declined', 'Assignment', 'Update'
        ) NULL");

        // Revert reservation_history.action to a safer subset used previously (keep most comprehensive prior set without newly added ones)
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
};
