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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
        ) NOT NULL");
    }
};
