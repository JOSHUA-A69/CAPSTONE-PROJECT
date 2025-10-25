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
        // Reservations table indexes
        Schema::table('reservations', function (Blueprint $table) {
            // Index for status queries (frequently filtered)
            $table->index('status', 'idx_reservations_status');

            // Index for user lookups
            $table->index('user_id', 'idx_reservations_user_id');

            // Index for priest/officiant lookups
            $table->index('officiant_id', 'idx_reservations_officiant_id');

            // Index for organization lookups
            $table->index('org_id', 'idx_reservations_org_id');

            // Index for schedule date queries (date range searches)
            $table->index('schedule_date', 'idx_reservations_schedule_date');

            // Composite index for common queries
            $table->index(['status', 'schedule_date'], 'idx_reservations_status_date');
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Index for role-based queries
            $table->index('role', 'idx_users_role');

            // Index for account status
            $table->index('account_status', 'idx_users_account_status');

            // Composite index for active users by role
            $table->index(['role', 'account_status'], 'idx_users_role_status');
        });

        // Organizations table indexes
        Schema::table('organizations', function (Blueprint $table) {
            // Index for adviser lookups
            $table->index('adviser_id', 'idx_organizations_adviser_id');
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            // Index for user notifications
            $table->index('user_id', 'idx_notifications_user_id');

            // Index for unread notifications
            $table->index('is_read', 'idx_notifications_is_read');

            // Composite index for user's unread notifications
            $table->index(['user_id', 'is_read'], 'idx_notifications_user_unread');

            // Index for created_at (for ordering)
            $table->index('created_at', 'idx_notifications_created_at');
        });

        // Chat messages table indexes
        Schema::table('chat_messages', function (Blueprint $table) {
            // Index for conversation lookups
            $table->index('chat_conversation_id', 'idx_chat_messages_conversation_id');

            // Index for sender lookups
            $table->index('sender_id', 'idx_chat_messages_sender_id');

            // Index for timestamp ordering
            $table->index('created_at', 'idx_chat_messages_created_at');
        });

        // Reservation history table indexes
        Schema::table('reservation_history', function (Blueprint $table) {
            // Index for reservation lookups
            $table->index('reservation_id', 'idx_reservation_history_res_id');

            // Index for user activity
            $table->index('performed_by', 'idx_reservation_history_performed_by');

            // Index for timestamp
            $table->index('created_at', 'idx_reservation_history_created_at');
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
            $table->dropIndex('idx_users_account_status');
            $table->dropIndex('idx_users_role_status');
        });

        // Organizations table
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('idx_organizations_adviser_id');
        });

        // Notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_id');
            $table->dropIndex('idx_notifications_is_read');
            $table->dropIndex('idx_notifications_user_unread');
            $table->dropIndex('idx_notifications_created_at');
        });

        // Chat messages table
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex('idx_chat_messages_conversation_id');
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
