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
        // No-op: An earlier migration (2025_10_11_000004_create_reservations_and_history_tables)
        // creates the `reservations` table with the correct schema. This stub migration
        // would conflict by attempting to recreate the table. Intentionally left empty.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: Do not drop `reservations` here to avoid unintended data loss.
    }
};
