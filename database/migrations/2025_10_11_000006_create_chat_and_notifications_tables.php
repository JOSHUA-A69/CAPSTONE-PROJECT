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
        // Note: chat_messages table removed - using 'messages' table instead
        // See migration: 2025_10_24_133542_create_messages_table.php

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
    }
};
