<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates table for organization booking cancellation requests that require adviser approval.
     */
    public function up(): void
    {
        Schema::create('organization_booking_cancellations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_request_id');
            $table->unsignedBigInteger('requested_by');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('adviser_response')->nullable();
            $table->unsignedBigInteger('responded_by')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('booking_request_id')
                ->references('id')
                ->on('organization_booking_requests')
                ->onDelete('cascade');

            $table->foreign('requested_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('responded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });

        // Add cancellation_restricted flag to organization_booking_requests
        Schema::table('organization_booking_requests', function (Blueprint $table) {
            $table->boolean('cancellation_restricted')->default(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_booking_cancellations');

        Schema::table('organization_booking_requests', function (Blueprint $table) {
            $table->dropColumn('cancellation_restricted');
        });
    }
};
