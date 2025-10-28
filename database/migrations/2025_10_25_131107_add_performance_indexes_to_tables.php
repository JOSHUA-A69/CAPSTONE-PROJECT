<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists on a table (Doctrine-based, safe if DBAL present).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                $result = DB::selectOne(
                    "SELECT COUNT(1) AS cnt FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
                    [$table, $indexName]
                );
                return isset($result->cnt) ? ((int) $result->cnt > 0) : false;
            }

            // Fallback to Doctrine DBAL when available (other drivers)
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = array_change_key_case($sm->listTableIndexes($table), CASE_LOWER);
            return isset($indexes[strtolower($indexName)]);
        } catch (\Throwable $e) {
            // If doctrine not available, assume not exists to attempt creation
            return false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reservations table indexes
        Schema::table('reservations', function (Blueprint $table) {
            // Index for status queries (frequently filtered)
            if (!Schema::hasColumn('reservations', 'status') || !$this->indexExists('reservations', 'idx_reservations_status')) {
                $table->index('status', 'idx_reservations_status');
            }

            // Index for user lookups
            if (!Schema::hasColumn('reservations', 'user_id') || !$this->indexExists('reservations', 'idx_reservations_user_id')) {
                $table->index('user_id', 'idx_reservations_user_id');
            }

            // Index for priest/officiant lookups
            if (!Schema::hasColumn('reservations', 'officiant_id') || !$this->indexExists('reservations', 'idx_reservations_officiant_id')) {
                $table->index('officiant_id', 'idx_reservations_officiant_id');
            }

            // Index for organization lookups
            if (!Schema::hasColumn('reservations', 'org_id') || !$this->indexExists('reservations', 'idx_reservations_org_id')) {
                $table->index('org_id', 'idx_reservations_org_id');
            }

            // Index for schedule date queries (date range searches)
            if (!Schema::hasColumn('reservations', 'schedule_date') || !$this->indexExists('reservations', 'idx_reservations_schedule_date')) {
                $table->index('schedule_date', 'idx_reservations_schedule_date');
            }

            // Composite index for common queries
            if (!Schema::hasColumn('reservations', 'status') || !Schema::hasColumn('reservations', 'schedule_date') || !$this->indexExists('reservations', 'idx_reservations_status_date')) {
                $table->index(['status', 'schedule_date'], 'idx_reservations_status_date');
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Index for role-based queries
            if (!Schema::hasColumn('users', 'role') || !$this->indexExists('users', 'idx_users_role')) {
                $table->index('role', 'idx_users_role');
            }

            // Index for account status
            if (Schema::hasColumn('users', 'status') && !$this->indexExists('users', 'idx_users_status')) {
                $table->index('status', 'idx_users_status');
            }

            // Composite index for active users by role
            if (Schema::hasColumn('users', 'role') && Schema::hasColumn('users', 'status') && !$this->indexExists('users', 'idx_users_role_status')) {
                $table->index(['role', 'status'], 'idx_users_role_status');
            }
        });

        // Organizations table indexes
        Schema::table('organizations', function (Blueprint $table) {
            // Index for adviser lookups
            if (!Schema::hasColumn('organizations', 'adviser_id') || !$this->indexExists('organizations', 'idx_organizations_adviser_id')) {
                $table->index('adviser_id', 'idx_organizations_adviser_id');
            }
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            // Index for user notifications
            if (!Schema::hasColumn('notifications', 'user_id') || !$this->indexExists('notifications', 'idx_notifications_user_id')) {
                $table->index('user_id', 'idx_notifications_user_id');
            }

            // Index for read_at (used to determine unread when NULL)
            if (!Schema::hasColumn('notifications', 'read_at') || !$this->indexExists('notifications', 'idx_notifications_read_at')) {
                $table->index('read_at', 'idx_notifications_read_at');
            }

            // Composite index for user's read_at (NULL = unread)
            if (Schema::hasColumn('notifications', 'user_id') && Schema::hasColumn('notifications', 'read_at') && !$this->indexExists('notifications', 'idx_notifications_user_read_at')) {
                $table->index(['user_id', 'read_at'], 'idx_notifications_user_read_at');
            }

            // Index for created_at (for ordering)
            if (!Schema::hasColumn('notifications', 'created_at') || !$this->indexExists('notifications', 'idx_notifications_created_at')) {
                $table->index('created_at', 'idx_notifications_created_at');
            }
        });

        // Chat messages table indexes
        Schema::table('chat_messages', function (Blueprint $table) {
            // Index for sender lookups
            if (!Schema::hasColumn('chat_messages', 'sender_id') || !$this->indexExists('chat_messages', 'idx_chat_messages_sender_id')) {
                $table->index('sender_id', 'idx_chat_messages_sender_id');
            }

            // Index for timestamp ordering
            if (!Schema::hasColumn('chat_messages', 'created_at') || !$this->indexExists('chat_messages', 'idx_chat_messages_created_at')) {
                $table->index('created_at', 'idx_chat_messages_created_at');
            }
        });

        // Reservation history table indexes
        Schema::table('reservation_history', function (Blueprint $table) {
            // Index for reservation lookups
            if (!Schema::hasColumn('reservation_history', 'reservation_id') || !$this->indexExists('reservation_history', 'idx_reservation_history_res_id')) {
                $table->index('reservation_id', 'idx_reservation_history_res_id');
            }

            // Index for user activity
            if (!Schema::hasColumn('reservation_history', 'performed_by') || !$this->indexExists('reservation_history', 'idx_reservation_history_performed_by')) {
                $table->index('performed_by', 'idx_reservation_history_performed_by');
            }

            // Index for timestamp
            if (!Schema::hasColumn('reservation_history', 'created_at') || !$this->indexExists('reservation_history', 'idx_reservation_history_created_at')) {
                $table->index('created_at', 'idx_reservation_history_created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reservations table
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('idx_reservations_status');
            $table->dropIndex('idx_reservations_user_id');
            $table->dropIndex('idx_reservations_officiant_id');
            $table->dropIndex('idx_reservations_org_id');
            $table->dropIndex('idx_reservations_schedule_date');
            $table->dropIndex('idx_reservations_status_date');
        });

        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_status');
            $table->dropIndex('idx_users_role_status');
        });

        // Organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('idx_organizations_adviser_id');
        });

        // Notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_read_at');
            $table->dropIndex('idx_notifications_user_read_at');
            $table->dropIndex('idx_notifications_created_at');
        });

        // Chat messages table
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_messages_sender_id');
            $table->dropIndex('idx_chat_messages_created_at');
        });

        // Reservation history table
        Schema::table('reservation_history', function (Blueprint $table) {
            $table->dropIndex('idx_reservation_history_res_id');
            $table->dropIndex('idx_reservation_history_performed_by');
            $table->dropIndex('idx_reservation_history_created_at');
        });
    }
};
