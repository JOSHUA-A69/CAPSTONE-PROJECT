<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates organization_booking_requests table to handle the "Manage Organization"
     * use case where requestors can book organization services with adviser approval.
     */
    public function up(): void
    {
        Schema::create('organization_booking_requests', function (Blueprint $table) {
            $table->id();
            
            // Requestor information
            $table->foreignId('requestor_id')->constrained('users')->onDelete('cascade');
            
            // Organization selection
            $table->unsignedBigInteger('organization_id');
            $table->foreign('organization_id')->references('org_id')->on('organizations')->onDelete('cascade');
            
            // Request details
            $table->string('activity_name');
            $table->text('purpose');
            $table->text('activity_details')->nullable();
            $table->datetime('requested_date');
            $table->string('requested_venue')->nullable();
            $table->integer('estimated_participants')->nullable();
            $table->text('special_requirements')->nullable();
            
            // Request status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('adviser_comments')->nullable();
            
            // Tracking timestamps
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('adviser_notified_at')->nullable();
            $table->timestamp('adviser_responded_at')->nullable();
            $table->timestamp('staff_reminded_at')->nullable();
            
            // Actor tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['organization_id', 'status']);
            $table->index(['requestor_id', 'created_at']);
            $table->index(['status', 'adviser_notified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_booking_requests');
    }
};