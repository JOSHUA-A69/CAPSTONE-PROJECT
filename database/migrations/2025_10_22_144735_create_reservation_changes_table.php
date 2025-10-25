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
        Schema::create('reservation_changes', function (Blueprint $table) {
            $table->id('change_id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('requested_by'); // requestor who requested the change
            $table->text('changes_requested'); // JSON of old vs new values
            $table->text('requestor_notes')->nullable(); // why they want to change
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable(); // admin who reviewed
            $table->text('rejection_reason')->nullable(); // if rejected, why
            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_changes');
    }
};
