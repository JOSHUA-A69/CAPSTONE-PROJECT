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
        Schema::table('reservations', function (Blueprint $table) {
            // Add columns only if they do not already exist (idempotent / safe re-run)
            if (!Schema::hasColumn('reservations', 'activity_name')) {
                $table->string('activity_name')->nullable()->after('schedule_date')
                    ->comment('Name/title of the spiritual activity or event');
            }

            if (!Schema::hasColumn('reservations', 'theme')) {
                $table->text('theme')->nullable()->after('activity_name')
                    ->comment('Theme or spiritual message of the activity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Drop columns only if they exist
            if (Schema::hasColumn('reservations', 'activity_name')) {
                $table->dropColumn('activity_name');
            }
            if (Schema::hasColumn('reservations', 'theme')) {
                $table->dropColumn('theme');
            }
        });
    }
};
