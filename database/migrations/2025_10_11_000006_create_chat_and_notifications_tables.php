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
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id('message_id');
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('reservation_id')->nullable();
                $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('set null');
                $table->text('message_content');
                $table->dateTime('sent_at')->nullable();
                $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id('notification_id');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('reservation_id')->nullable();
                $table->foreign('reservation_id')->references('reservation_id')->on('reservations')->onDelete('set null');
                $table->text('message');
                $table->enum('type', ['Approval', 'Reminder', 'System Alert'])->nullable();
                $table->dateTime('sent_at')->nullable();
                $table->dateTime('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('chat_messages');
    }
};
