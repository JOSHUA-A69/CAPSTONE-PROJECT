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
            // Ministry volunteers (new fields)
            if (!Schema::hasColumn('reservations', 'commentator')) {
                $table->string('commentator')->nullable()->after('participants_count');
            }
            if (!Schema::hasColumn('reservations', 'servers')) {
                $table->text('servers')->nullable()->after('commentator');
            }
            if (!Schema::hasColumn('reservations', 'readers')) {
                $table->text('readers')->nullable()->after('servers');
            }
            if (!Schema::hasColumn('reservations', 'choir')) {
                $table->string('choir')->nullable()->after('readers');
            }
            if (!Schema::hasColumn('reservations', 'psalmist')) {
                $table->string('psalmist')->nullable()->after('choir');
            }
            if (!Schema::hasColumn('reservations', 'prayer_leader')) {
                $table->string('prayer_leader')->nullable()->after('psalmist');
            }

            // Requestor confirmation workflow
            if (!Schema::hasColumn('reservations', 'contacted_at')) {
                $table->timestamp('contacted_at')->nullable()->after('adviser_responded_at');
            }
            if (!Schema::hasColumn('reservations', 'requestor_confirmed_at')) {
                $table->timestamp('requestor_confirmed_at')->nullable()->after('contacted_at');
            }
            if (!Schema::hasColumn('reservations', 'requestor_confirmation_token')) {
                $table->string('requestor_confirmation_token')->nullable()->after('requestor_confirmed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $columns = [
                'commentator',
                'servers',
                'readers',
                'choir',
                'psalmist',
                'prayer_leader',
                'contacted_at',
                'requestor_confirmed_at',
                'requestor_confirmation_token'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('reservations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
