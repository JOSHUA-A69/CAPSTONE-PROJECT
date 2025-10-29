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
            try { $table->index('status', 'idx_reservations_status'); } catch (\Exception $e) {}
            try { $table->index('user_id', 'idx_reservations_user_id'); } catch (\Exception $e) {}
            try { $table->index('officiant_id', 'idx_reservations_officiant_id'); } catch (\Exception $e) {}
            try { $table->index('org_id', 'idx_reservations_org_id'); } catch (\Exception $e) {}
            try { $table->index('schedule_date', 'idx_reservations_schedule_date'); } catch (\Exception $e) {}
            try { $table->index(['status', 'schedule_date'], 'idx_reservations_status_date'); } catch (\Exception $e) {}
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            try { $table->index('role', 'idx_users_role'); } catch (\Exception $e) {}
            try { $table->index('account_status', 'idx_users_account_status'); } catch (\Exception $e) {}
            try { $table->index(['role', 'account_status'], 'idx_users_role_status'); } catch (\Exception $e) {}
        });

        // Organizations table indexes
        Schema::table('organizations', function (Blueprint $table) {
            try { $table->index('adviser_id', 'idx_organizations_adviser_id'); } catch (\Exception $e) {}
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            try { $table->index('user_id', 'idx_notifications_user_id'); } catch (\Exception $e) {}
            try { $table->index('is_read', 'idx_notifications_is_read'); } catch (\Exception $e) {}
            try { $table->index(['user_id', 'is_read'], 'idx_notifications_user_unread'); } catch (\Exception $e) {}
            try { $table->index('created_at', 'idx_notifications_created_at'); } catch (\Exception $e) {}
        });

        // Note: chat_messages table removed - using 'messages' table instead
        // Messages table already has indexes in its creation migration

        // Reservation history table indexes
        Schema::table('reservation_history', function (Blueprint $table) {
            try { $table->index('reservation_id', 'idx_reservation_history_res_id'); } catch (\Exception $e) {}
            try { $table->index('performed_by', 'idx_reservation_history_performed_by'); } catch (\Exception $e) {}
            try { $table->index('created_at', 'idx_reservation_history_created_at'); } catch (\Exception $e) {}
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

        // Note: chat_messages table removed - no indexes to drop

        // Reservation history table
        Schema::table('reservation_history', function (Blueprint $table) {
            $table->dropIndex('idx_reservation_history_res_id');
            $table->dropIndex('idx_reservation_history_performed_by');
            $table->dropIndex('idx_reservation_history_created_at');
        });
    }
};
