<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'preferred_officiant_id')) {
                $table->unsignedBigInteger('preferred_officiant_id')->nullable()->after('officiant_id');
                $table->foreign('preferred_officiant_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'preferred_officiant_id')) {
                $table->dropForeign(['preferred_officiant_id']);
                $table->dropColumn('preferred_officiant_id');
            }
        });
    }
};
