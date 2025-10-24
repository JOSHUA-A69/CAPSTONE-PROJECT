<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Create normalized table for ministry roles/volunteers
        if (!Schema::hasTable('reservation_ministry_roles')) {
            Schema::create('reservation_ministry_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('reservation_id');
                $table->string('role', 50); // commentator, server, reader, choir, psalmist, prayer_leader
                $table->string('name');
                $table->timestamps();

                $table->foreign('reservation_id')
                    ->references('reservation_id')
                    ->on('reservations')
                    ->onDelete('cascade');

                $table->index(['reservation_id', 'role']);
            });
        }

        // 2) Migrate data from columns into normalized rows (best-effort)
        if (Schema::hasTable('reservations')) {
            $columns = DB::getSchemaBuilder()->getColumnListing('reservations');
            $has = fn($c) => in_array($c, $columns);

            $reservations = DB::table('reservations')->select(
                'reservation_id',
                $has('commentator') ? 'commentator' : DB::raw('NULL as commentator'),
                $has('servers') ? 'servers' : DB::raw('NULL as servers'),
                $has('readers') ? 'readers' : DB::raw('NULL as readers'),
                $has('choir') ? 'choir' : DB::raw('NULL as choir'),
                $has('psalmist') ? 'psalmist' : DB::raw('NULL as psalmist'),
                $has('prayer_leader') ? 'prayer_leader' : DB::raw('NULL as prayer_leader')
            )->get();

            foreach ($reservations as $r) {
                $inserts = [];
                $now = now();

                $add = function($role, $value) use (&$inserts, $r, $now) {
                    if (!is_null($value) && trim($value) !== '') {
                        // Split comma/newline separated lists for multi-person roles
                        $parts = preg_split('/[,\n]+/', $value);
                        foreach ($parts as $name) {
                            $name = trim($name);
                            if ($name !== '') {
                                $inserts[] = [
                                    'reservation_id' => $r->reservation_id,
                                    'role' => $role,
                                    'name' => $name,
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                        }
                    }
                };

                $add('commentator', $r->commentator ?? null);
                $add('server', $r->servers ?? null);
                $add('reader', $r->readers ?? null);
                $add('choir', $r->choir ?? null);
                $add('psalmist', $r->psalmist ?? null);
                $add('prayer_leader', $r->prayer_leader ?? null);

                if (!empty($inserts)) {
                    DB::table('reservation_ministry_roles')->insert($inserts);
                }
            }
        }

        // 3) Drop columns from reservations
        Schema::table('reservations', function (Blueprint $table) {
            $dropIf = function ($col) use ($table) {
                if (Schema::hasColumn('reservations', $col)) {
                    $table->dropColumn($col);
                }
            };
            $dropIf('commentator');
            $dropIf('servers');
            $dropIf('readers');
            $dropIf('choir');
            $dropIf('psalmist');
            $dropIf('prayer_leader');
        });
    }

    public function down(): void
    {
        // Recreate columns (without restoring data linkage) for rollback
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'commentator')) {
                $table->string('commentator')->nullable()->after('participants_count');
            }
            if (!Schema::hasColumn('reservations', 'servers')) {
                $table->text('servers')->nullable()->after('commentator');
            }
            if (!Schema::hasColumn('reservations', 'readers')) {
                $table->text('readers')->nullable()->after('servers');
            }
            if (!Schema::hasColumn('reservations', 'choir')) {
                $table->string('choir')->nullable()->after('readers');
            }
            if (!Schema::hasColumn('reservations', 'psalmist')) {
                $table->string('psalmist')->nullable()->after('choir');
            }
            if (!Schema::hasColumn('reservations', 'prayer_leader')) {
                $table->string('prayer_leader')->nullable()->after('psalmist');
            }
        });

        Schema::dropIfExists('reservation_ministry_roles');
    }
};
