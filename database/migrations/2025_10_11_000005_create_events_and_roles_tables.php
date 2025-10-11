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
        Schema::create('events', function (Blueprint $table) {
            $table->id('event_id');
            $table->unsignedBigInteger('reservation_id');
            $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('cascade');
            $table->unsignedBigInteger('venue_id');
            $table->foreign('venue_id')->references('venue_id')->on('venues')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('date_time');
            $table->integer('duration')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });

        Schema::create('event_roles', function (Blueprint $table) {
            $table->id('event_role_id');
            $table->enum('role_name', [
                'reader',
                'psalmist',
                'commentator',
                'prayer leader',
                'presider',
                'server',
                'choir',
            ]);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('event_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('event_id');
            $table->foreign('event_id')->references('event_id')->on('events')->onDelete('cascade');
            $table->unsignedBigInteger('event_role_id');
            $table->foreign('event_role_id')->references('event_role_id')->on('event_roles')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('assigned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_assignments');
        Schema::dropIfExists('event_roles');
        Schema::dropIfExists('events');
    }
};
