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
        if (!Schema::hasTable('reservation_organization')) {
            return;
        }

        Schema::table('reservation_organization', function (Blueprint $table) {
            if (!Schema::hasColumn('reservation_organization', 'approval_status')) {
                $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('notified_at');
            }

            if (!Schema::hasColumn('reservation_organization', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approval_status');
            }

            if (!Schema::hasColumn('reservation_organization', 'responded_by')) {
                $table->unsignedBigInteger('responded_by')->nullable()->after('rejection_reason');
            }

            if (!Schema::hasColumn('reservation_organization', 'responded_at')) {
                $table->timestamp('responded_at')->nullable()->after('responded_by');
            }
        });

        if (
            Schema::hasColumn('reservation_organization', 'responded_by')
            && !Schema::hasColumn('reservation_organization', 'responded_at')
        ) {
            Schema::table('reservation_organization', function (Blueprint $table) {
                $table->timestamp('responded_at')->nullable()->after('responded_by');
            });
        }

        try {
            Schema::table('reservation_organization', function (Blueprint $table) {
                $table->foreign('responded_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // Ignore if the foreign key already exists or cannot be applied in this environment.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('reservation_organization')) {
            return;
        }

        try {
            Schema::table('reservation_organization', function (Blueprint $table) {
                $table->dropForeign(['responded_by']);
            });
        } catch (\Throwable $e) {
            // Ignore if foreign key doesn't exist.
        }

        Schema::table('reservation_organization', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['approval_status', 'rejection_reason', 'responded_by', 'responded_at'] as $column) {
                if (Schema::hasColumn('reservation_organization', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
