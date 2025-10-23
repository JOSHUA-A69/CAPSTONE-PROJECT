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
        Schema::create('reservation_cancellations', function (Blueprint $table) {
            $table->id('cancellation_id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('requestor_id'); // User who requested cancellation
            $table->text('reason'); // Cancellation reason
            $table->enum('status', [
                'pending',
                'confirmed_by_staff',
                'confirmed_by_admin',
                'confirmed_by_adviser',
                'confirmed_by_priest',
                'completed',
                'rejected'
            ])->default('pending');
            
            // Confirmation timestamps
            $table->timestamp('staff_confirmed_at')->nullable();
            $table->unsignedBigInteger('staff_confirmed_by')->nullable();
            $table->timestamp('admin_confirmed_at')->nullable();
            $table->unsignedBigInteger('admin_confirmed_by')->nullable();
            $table->timestamp('adviser_confirmed_at')->nullable();
            $table->unsignedBigInteger('adviser_confirmed_by')->nullable();
            $table->timestamp('priest_confirmed_at')->nullable();
            $table->unsignedBigInteger('priest_confirmed_by')->nullable();
            
            // Notification tracking
            $table->timestamp('adviser_notified_at')->nullable();
            $table->timestamp('priest_notified_at')->nullable();
            $table->timestamp('staff_escalated_adviser_at')->nullable(); // When staff was notified due to adviser timeout
            $table->timestamp('staff_escalated_priest_at')->nullable(); // When staff was notified due to priest timeout
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
            $table->foreign('requestor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('staff_confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('admin_confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('adviser_confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('priest_confirmed_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('reservation_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_cancellations');
    }
};
