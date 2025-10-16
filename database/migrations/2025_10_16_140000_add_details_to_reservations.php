<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (!Schema::hasColumn('reservations', 'purpose')) {
                    $table->string('purpose', 150)->nullable()->after('status');
                }
                if (!Schema::hasColumn('reservations', 'details')) {
                    $table->text('details')->nullable()->after('purpose');
                }
                if (!Schema::hasColumn('reservations', 'participants_count')) {
                    $table->integer('participants_count')->nullable()->after('details');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (Schema::hasColumn('reservations', 'participants_count')) {
                    $table->dropColumn('participants_count');
                }
                if (Schema::hasColumn('reservations', 'details')) {
                    $table->dropColumn('details');
                }
                if (Schema::hasColumn('reservations', 'purpose')) {
                    $table->dropColumn('purpose');
                }
            });
        }
    }
};
