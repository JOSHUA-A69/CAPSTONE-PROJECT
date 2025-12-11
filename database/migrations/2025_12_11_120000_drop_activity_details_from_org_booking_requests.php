<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organization_booking_requests') && Schema::hasColumn('organization_booking_requests', 'activity_details')) {
            Schema::table('organization_booking_requests', function (Blueprint $table) {
                $table->dropColumn('activity_details');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('organization_booking_requests') && !Schema::hasColumn('organization_booking_requests', 'activity_details')) {
            Schema::table('organization_booking_requests', function (Blueprint $table) {
                $table->text('activity_details')->nullable()->after('purpose');
            });
        }
    }
};
