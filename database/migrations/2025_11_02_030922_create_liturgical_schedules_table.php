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
        Schema::create('liturgical_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('schedule_date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->enum('event_type', ['mass', 'confession', 'adoration', 'retreat', 'seminar', 'meeting', 'celebration', 'other'])->default('other');
            $table->boolean('is_public')->default(true); // Visible on public calendar
            $table->unsignedBigInteger('created_by'); // Staff who created
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('schedule_date');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liturgical_schedules');
    }
};
