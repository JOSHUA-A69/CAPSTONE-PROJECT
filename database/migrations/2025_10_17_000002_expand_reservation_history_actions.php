<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Expand reservation_history action enum to track all workflow steps
     */
    public function up(): void
    {
        // Get the current database driver
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'submitted',
                'adviser_notified',
                'adviser_approved',
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
            DB::statement("ALTER TABLE reservation_history MODIFY COLUMN action ENUM(
                'created',
                'updated',
                'approved',
                'cancelled'
            ) NOT NULL");
        }
    }
};
