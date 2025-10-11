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
        if (Schema::hasTable('organizations')) {
            // add new org_desc column as TEXT nullable
            Schema::table('organizations', function (Blueprint $table) {
                $table->text('org_desc')->nullable()->after('org_name');
            });

            // copy existing org_type values into org_desc
            DB::statement("UPDATE organizations SET org_desc = org_type WHERE org_type IS NOT NULL");

            // drop old org_type column if exists
            Schema::table('organizations', function (Blueprint $table) {
                if (Schema::hasColumn('organizations', 'org_type')) {
                    $table->dropColumn('org_type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('organizations')) {
            Schema::table('organizations', function (Blueprint $table) {
                $table->string('org_type', 50)->nullable()->after('org_name');
            });

            // copy back from org_desc to org_type (truncate to 50 chars)
            DB::statement("UPDATE organizations SET org_type = LEFT(org_desc, 50) WHERE org_desc IS NOT NULL");

            Schema::table('organizations', function (Blueprint $table) {
                if (Schema::hasColumn('organizations', 'org_desc')) {
                    $table->dropColumn('org_desc');
                }
            });
        }
    }
};
