<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates pivot table for multiple organization selection in organization booking requests.
     */
    public function up(): void
    {
        Schema::create('organization_booking_organizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_request_id');
            $table->unsignedBigInteger('organization_id');
            $table->boolean('is_primary')->default(false);
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('responded_by')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->foreign('booking_request_id')
                ->references('id')
                ->on('organization_booking_requests')
                ->onDelete('cascade');

            $table->foreign('organization_id')
                ->references('org_id')
                ->on('organizations')
                ->onDelete('cascade');

            $table->foreign('responded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->unique(['booking_request_id', 'organization_id'], 'org_booking_orgs_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_booking_organizations');
    }
};
