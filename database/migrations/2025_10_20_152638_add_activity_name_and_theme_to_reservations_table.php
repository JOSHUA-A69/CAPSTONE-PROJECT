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
            // Add activity name and theme columns that were missing
            $table->string('activity_name')->nullable()->after('schedule_date')
                ->comment('Name/title of the spiritual activity or event');

            $table->text('theme')->nullable()->after('activity_name')
                ->comment('Theme or spiritual message of the activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['activity_name', 'theme']);
        });
    }
};
