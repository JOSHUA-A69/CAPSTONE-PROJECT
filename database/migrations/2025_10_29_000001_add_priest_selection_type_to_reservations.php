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
            // Add priest selection type column
            $table->enum('priest_selection_type', ['specific', 'any_available', 'external'])
                ->default('specific')
                ->after('officiant_id')
                ->comment('Type of priest selection: specific (choose from list), any_available (admin assigns), external (already have priest)');
            
            // Add external priest details
            $table->string('external_priest_name')->nullable()->after('priest_selection_type');
            $table->string('external_priest_contact')->nullable()->after('external_priest_name');
            
            // Make officiant_id nullable since it might not be set initially for any_available or external
            $table->unsignedBigInteger('officiant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['priest_selection_type', 'external_priest_name', 'external_priest_contact']);
        });
    }
};
