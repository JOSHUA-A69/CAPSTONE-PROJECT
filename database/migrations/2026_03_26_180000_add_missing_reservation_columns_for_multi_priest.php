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
        if (Schema::hasTable('reservation_priest') && !Schema::hasColumn('reservation_priest', 'is_main_celebrant')) {
            Schema::table('reservation_priest', function (Blueprint $table) {
                $table->boolean('is_main_celebrant')->default(false)->after('priest_id');
            });
        }

        if (Schema::hasTable('reservations') && !Schema::hasColumn('reservations', 'end_time')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dateTime('end_time')->nullable()->after('schedule_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reservation_priest') && Schema::hasColumn('reservation_priest', 'is_main_celebrant')) {
            Schema::table('reservation_priest', function (Blueprint $table) {
                $table->dropColumn('is_main_celebrant');
            });
        }

        if (Schema::hasTable('reservations') && Schema::hasColumn('reservations', 'end_time')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('end_time');
            });
        }
    }
};
