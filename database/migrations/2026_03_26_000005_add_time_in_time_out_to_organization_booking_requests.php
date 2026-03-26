<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organization_booking_requests', function (Blueprint $table) {
            // Add time_in and time_out fields if they don't exist
            if (!Schema::hasColumn('organization_booking_requests', 'time_in')) {
                $table->time('time_in')->nullable()->after('requested_date');
            }
            if (!Schema::hasColumn('organization_booking_requests', 'time_out')) {
                $table->time('time_out')->nullable()->after('time_in');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_booking_requests', function (Blueprint $table) {
            $table->dropColumn(['time_in', 'time_out']);
        });
    }
};
