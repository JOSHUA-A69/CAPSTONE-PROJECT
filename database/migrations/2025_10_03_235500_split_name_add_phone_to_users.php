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
        // Add new columns
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->after('id');
            }
            if (! Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->after('middle_name');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });

        // Migrate existing `name` values into first/middle/last
        $users = DB::table('users')->select('id', 'name')->get();
        foreach ($users as $user) {
            $name = trim($user->name ?? '');
            if ($name === '') {
                // leave first/middle/last null
                continue;
            }

            // split on whitespace
            $parts = preg_split('/\s+/', $name);
            $count = count($parts);

            if ($count === 1) {
                $first = $parts[0];
                $middle = null;
                $last = '';
            } elseif ($count === 2) {
                $first = $parts[0];
                $middle = null;
                $last = $parts[1];
            } else {
                $first = $parts[0];
                $last = $parts[$count - 1];
                $middle = implode(' ', array_slice($parts, 1, $count - 2));
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update(['first_name' => $first, 'middle_name' => $middle, 'last_name' => $last]);
        }

        // Drop the old `name` column
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate `name` and populate it from first/middle/last
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
        });

        // Populate name using CONCAT_WS to handle nulls
        DB::statement('UPDATE users SET name = CONCAT_WS(" ", first_name, middle_name, last_name)');

        // Drop the added columns
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('users', 'middle_name')) {
                $table->dropColumn('middle_name');
            }
            if (Schema::hasColumn('users', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
