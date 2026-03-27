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
        Schema::table('organization_booking_organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_booking_organizations', 'server_quantity')) {
                $table->unsignedTinyInteger('server_quantity')
                    ->default(1)
                    ->after('is_primary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization_booking_organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organization_booking_organizations', 'server_quantity')) {
                $table->dropColumn('server_quantity');
            }
        });
    }
};
