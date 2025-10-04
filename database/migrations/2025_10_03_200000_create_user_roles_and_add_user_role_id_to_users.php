<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create user_roles table
        Schema::create('user_roles', function (Blueprint $table) {
            $table->bigIncrements('user_role_id');
            $table->string('role_name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed default roles
        $now = now();
        // Use role_name values that match the existing users.role enum
        // so the mapping UPDATE statement will find matches.
        $roles = [
            ['role_name' => 'admin', 'description' => 'CREaM Administrator - highest-level user; manages system-wide settings, users, and reports.', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'staff', 'description' => 'CREaM Staff - supports daily operations, processing reservations and generating reports.', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'adviser', 'description' => 'Organization Adviser - reviews and approves requests for their organization and views related reports.', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'requestor', 'description' => 'Requestor - submits requests for services and tracks approval status.', 'created_at' => $now, 'updated_at' => $now],
            ['role_name' => 'priest', 'description' => 'Priest - officiant assigned to approved events and reviews event details.', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('user_roles')->insert($roles);

        // Add FK column to users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_role_id')->nullable()->after('role');
            $table->foreign('user_role_id')->references('user_role_id')->on('user_roles')->onDelete('set null');
        });

        // Map existing users.role (enum) to the new user_role_id
        DB::statement("UPDATE users u JOIN user_roles r ON u.role = r.role_name SET u.user_role_id = r.user_role_id");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove FK and column
        if (Schema::hasColumn('users', 'user_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['user_role_id']);
                $table->dropColumn('user_role_id');
            });
        }

        Schema::dropIfExists('user_roles');
    }
};
