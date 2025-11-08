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
            if (!Schema::hasColumn('liturgical_schedules', 'external_priest_name')) {
                $table->string('external_priest_name', 100)->nullable()->after('priest_id');
            }
            if (!Schema::hasColumn('liturgical_schedules', 'external_priest_contact')) {
                $table->string('external_priest_contact', 100)->nullable()->after('external_priest_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('liturgical_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('liturgical_schedules', 'external_priest_contact')) {
                $table->dropColumn('external_priest_contact');
            }
            if (Schema::hasColumn('liturgical_schedules', 'external_priest_name')) {
                $table->dropColumn('external_priest_name');
            }
        });
    }
};
