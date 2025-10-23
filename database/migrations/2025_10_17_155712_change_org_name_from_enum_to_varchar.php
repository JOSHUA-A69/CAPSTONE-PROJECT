<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change org_name from ENUM to VARCHAR to allow custom organization names
        DB::statement('ALTER TABLE organizations MODIFY org_name VARCHAR(255) NOT NULL');

        // Remove the unique constraint if it exists
        try {
            DB::statement('ALTER TABLE organizations DROP INDEX org_name');
        } catch (\Exception $e) {
            // Index might not exist, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to ENUM (only if you want to support rollback)
        $allowedValues = [
            'Himig Diwa Chorale',
            'Acolytes and Lectors',
            'Children of Mary',
            'Student Catholic Action',
            'Young Missionaries Club',
            'Catechetical Organization',
        ];

        $enumList = "'" . implode("','", $allowedValues) . "'";
        DB::statement("ALTER TABLE organizations MODIFY org_name ENUM($enumList) NOT NULL");
    }
};
