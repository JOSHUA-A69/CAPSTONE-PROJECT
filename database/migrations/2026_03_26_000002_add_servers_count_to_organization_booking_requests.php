<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds servers_needed field and session timeout tracking to organization booking requests.
     */
    public function up(): void
    {
        Schema::table('organization_booking_requests', function (Blueprint $table) {
            // Number of servers needed for the activity
            $table->unsignedInteger('servers_needed')->nullable()->after('estimated_participants');

            // Session timeout tracking
            $table->timestamp('session_started_at')->nullable()->after('submitted_at');
            $table->timestamp('session_expires_at')->nullable()->after('session_started_at');
            $table->boolean('session_expired')->default(false)->after('session_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_booking_requests', function (Blueprint $table) {
            $table->dropColumn(['servers_needed', 'session_started_at', 'session_expires_at', 'session_expired']);
        });
    }
};
