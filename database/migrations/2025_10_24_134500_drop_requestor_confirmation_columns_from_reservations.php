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
            if (Schema::hasColumn('reservations', 'contacted_at')) {
                $table->dropColumn('contacted_at');
            }
            if (Schema::hasColumn('reservations', 'requestor_confirmed_at')) {
                $table->dropColumn('requestor_confirmed_at');
            }
            if (Schema::hasColumn('reservations', 'requestor_confirmation_token')) {
                $table->dropColumn('requestor_confirmation_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'contacted_at')) {
                $table->dateTime('contacted_at')->nullable()->after('adviser_responded_at');
            }
            if (!Schema::hasColumn('reservations', 'requestor_confirmed_at')) {
                $table->dateTime('requestor_confirmed_at')->nullable()->after('contacted_at');
            }
            if (!Schema::hasColumn('reservations', 'requestor_confirmation_token')) {
                $table->string('requestor_confirmation_token', 128)->nullable()->after('requestor_confirmed_at');
            }
        });
    }
};
