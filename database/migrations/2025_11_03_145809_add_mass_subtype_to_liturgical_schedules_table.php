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
        Schema::table('liturgical_schedules', function (Blueprint $table) {
            $table->string('mass_subtype')->nullable()->after('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liturgical_schedules', function (Blueprint $table) {
            $table->dropColumn('mass_subtype');
        });
    }
};
