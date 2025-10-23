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
        // Expand the status enum to support full priest confirmation workflow
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM(
            'pending',
            'adviser_approved',
            'admin_approved',
            'pending_requestor_confirmation',
            'requestor_confirmed',
            'pending_priest_confirmation',
            'pending_priest_reassignment',
            'approved',
            'rejected',
            'cancelled',
            'completed'
        ) DEFAULT 'pending'");

        // Expand history action enum
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous values
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM(
            'pending',
            'adviser_approved',
            'admin_approved',
            'approved',
            'rejected',
            'cancelled'
        ) DEFAULT 'pending'");

        DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
            'created',
            'updated',
            'approved',
            'adviser_approved',
            'admin_approved',
            'rejected',
            'cancelled'
        )");
    }
};
