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
                if (!Schema::hasColumn('reservations', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->after('admin_notified_at')
                        ->constrained('users')->nullOnDelete()
                        ->comment('Admin/staff user who approved (e.g., assigned priest)');
                }
                if (!Schema::hasColumn('reservations', 'rejected_by')) {
                    $table->foreignId('rejected_by')->nullable()->after('approved_by')
                        ->constrained('users')->nullOnDelete()
                        ->comment('Admin/staff user who rejected the reservation');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                if (Schema::hasColumn('reservations', 'approved_by')) {
                    $table->dropForeign(['approved_by']);
                    $table->dropColumn('approved_by');
                }
                if (Schema::hasColumn('reservations', 'rejected_by')) {
                    $table->dropForeign(['rejected_by']);
                    $table->dropColumn('rejected_by');
                }
            });
        }
    }
};
