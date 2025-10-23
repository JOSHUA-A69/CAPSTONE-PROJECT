<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration enhances the reservations table to support the complete
     * workflow outlined in the Manage Reservation Request swim lane diagram:
     * - Priest/officiant assignment
     * - Adviser notification tracking
     * - Staff follow-up monitoring
     * - Priest confirmation status
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Priest/Officiant Assignment
            $table->foreignId('officiant_id')->nullable()->after('service_id')
                ->constrained('users')->onDelete('set null')
                ->comment('Priest assigned to officiate this service');

            // Adviser Notification Tracking
            $table->timestamp('adviser_notified_at')->nullable()->after('status')
                ->comment('When the organization adviser was first notified');

            $table->timestamp('adviser_responded_at')->nullable()->after('adviser_notified_at')
                ->comment('When the adviser approved or rejected');

            // Admin/Staff Tracking
            $table->timestamp('admin_notified_at')->nullable()->after('adviser_responded_at')
                ->comment('When CREaM admin was notified (after adviser approval)');

            $table->timestamp('staff_followed_up_at')->nullable()->after('admin_notified_at')
                ->comment('When staff sent follow-up for unnoticed requests');

            // Priest Confirmation Tracking
            $table->timestamp('priest_notified_at')->nullable()->after('staff_followed_up_at')
                ->comment('When priest was notified of assignment');

            $table->enum('priest_confirmation', ['pending', 'confirmed', 'declined'])->nullable()->after('priest_notified_at')
                ->comment('Priest availability confirmation status');

            $table->timestamp('priest_confirmed_at')->nullable()->after('priest_confirmation')
                ->comment('When priest confirmed or declined');

            // Cancellation tracking
            $table->text('cancellation_reason')->nullable()->after('priest_confirmed_at')
                ->comment('Reason provided for cancellation');

            $table->foreignId('cancelled_by')->nullable()->after('cancellation_reason')
                ->constrained('users')->onDelete('set null')
                ->comment('User who cancelled the reservation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['officiant_id']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'officiant_id',
                'adviser_notified_at',
                'adviser_responded_at',
                'admin_notified_at',
                'staff_followed_up_at',
                'priest_notified_at',
                'priest_confirmation',
                'priest_confirmed_at',
                'cancellation_reason',
                'cancelled_by'
            ]);
        });
    }
};
