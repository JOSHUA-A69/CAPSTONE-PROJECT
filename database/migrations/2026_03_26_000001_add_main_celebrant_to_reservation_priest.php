<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds is_main_celebrant flag to reservation_priest pivot table
     * to identify the main celebrant when multiple priests are assigned.
     */
    public function up(): void
    {
        Schema::table('reservation_priest', function (Blueprint $table) {
            $table->boolean('is_main_celebrant')->default(false)->after('priest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_priest', function (Blueprint $table) {
            $table->dropColumn('is_main_celebrant');
        });
    }
};
