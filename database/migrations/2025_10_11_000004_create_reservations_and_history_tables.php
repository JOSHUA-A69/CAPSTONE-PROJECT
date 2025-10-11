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
        if (!Schema::hasTable('reservations')) {
            Schema::create('reservations', function (Blueprint $table) {
                $table->id('reservation_id');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                // organizations primary key is org_id
                $table->unsignedBigInteger('org_id')->nullable();
                $table->foreign('org_id')->references('org_id')->on('organizations')->onDelete('set null');
                // venues primary key is venue_id
                $table->unsignedBigInteger('venue_id');
                $table->foreign('venue_id')->references('venue_id')->on('venues')->onDelete('restrict');
                // services primary key is service_id
                $table->unsignedBigInteger('service_id');
                $table->foreign('service_id')->references('service_id')->on('services')->onDelete('restrict');
                $table->dateTime('schedule_date');
                $table->enum('status', ['pending', 'approved', 'cancelled'])->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('reservation_history')) {
            Schema::create('reservation_history', function (Blueprint $table) {
                $table->id('history_id');
                $table->unsignedBigInteger('reservation_id');
                $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
                $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->enum('action', ['created', 'updated', 'approved', 'cancelled']);
                $table->text('remarks')->nullable();
                $table->dateTime('performed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_history');
        Schema::dropIfExists('reservations');
    }
};
