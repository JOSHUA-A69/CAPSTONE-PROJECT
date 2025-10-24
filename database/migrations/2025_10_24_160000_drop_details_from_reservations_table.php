<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('reservations', 'details')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->dropColumn('details');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('reservations', 'details')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->text('details')->nullable()->after('purpose');
            });
        }
    }
};
