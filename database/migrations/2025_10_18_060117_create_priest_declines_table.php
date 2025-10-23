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
        Schema::create('priest_declines', function (Blueprint $table) {
            $table->id('decline_id');
            $table->unsignedBigInteger('reservation_id');
            $table->unsignedBigInteger('priest_id');
            $table->text('reason');
            $table->timestamp('declined_at');
            $table->string('reservation_activity_name')->nullable();
            $table->dateTime('reservation_schedule_date')->nullable();
            $table->string('reservation_venue')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
            $table->foreign('priest_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index('priest_id');
            $table->index('declined_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('priest_declines');
    }
};
