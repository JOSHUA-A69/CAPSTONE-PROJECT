<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'priest_cancelled_confirmation' to the action enum
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
            'status_updated'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum without 'priest_cancelled_confirmation'
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
            'priest_reassigned',
            'staff_followed_up',
            'status_updated'
        )");
    }
};

