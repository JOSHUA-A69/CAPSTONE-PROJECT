<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Tables targeted for removal. If a table does not exist (e.g., environment already cleaned), it will be skipped safely.
     */
    private array $obsoleteTables = [
        'noon_mass_schedules',
        'event_assignments',
        'event_roles',
        'events',
        'chat_messages', // Not originally documented; defensive drop if present.
    ];

    public function up(): void
    {
        // Drop in dependency-safe order (child tables first)
        foreach ($this->obsoleteTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }

    public function down(): void
    {
        // Down recreates minimal placeholder schema for accidental rollback; adjust as needed.
        if (!Schema::hasTable('noon_mass_schedules')) {
            Schema::create('noon_mass_schedules', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id('event_id');
                $table->unsignedBigInteger('reservation_id');
                $table->unsignedBigInteger('venue_id');
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('date_time');
                $table->integer('duration')->nullable();
                $table->enum('status', ['scheduled','completed','cancelled'])->default('scheduled');
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('event_roles')) {
            Schema::create('event_roles', function (Blueprint $table) {
                $table->id('event_role_id');
                $table->enum('role_name', ['reader','psalmist','commentator','prayer leader','presider','server','choir']);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('event_assignments')) {
            Schema::create('event_assignments', function (Blueprint $table) {
                $table->id('assignment_id');
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('event_role_id');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
                $table->dateTime('assigned_at')->nullable();
                $table->timestamps();
            });
        }
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sender_id');
                $table->unsignedBigInteger('receiver_id');
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }
};
