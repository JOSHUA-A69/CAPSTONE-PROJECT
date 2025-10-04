<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $roles = [
            ['role_name' => 'admin', 'description' => 'CREaM Administrator - highest-level user; manages system-wide settings, users, and reports.'],
            ['role_name' => 'staff', 'description' => 'CREaM Staff - supports daily operations, processing reservations and generating reports.'],
            ['role_name' => 'adviser', 'description' => 'Organization Adviser - reviews and approves requests for their organization and views related reports.'],
            ['role_name' => 'requestor', 'description' => 'Requestor - submits requests for services and tracks approval status.'],
            ['role_name' => 'priest', 'description' => 'Priest - officiant assigned to approved events and reviews event details.'],
        ];

        foreach ($roles as $role) {
            DB::table('user_roles')->updateOrInsert(
                ['role_name' => $role['role_name']],
                ['description' => $role['description'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally empty to avoid accidental deletion of roles. If you want
        // to remove these roles on rollback, implement deletion logic here.
    }
};
