<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Backfill liturgical_schedules.location from venues (prefer address, fallback to name)
        if (Schema::hasTable('liturgical_schedules') && Schema::hasTable('venues')) {
            try {
                DB::statement("
                    UPDATE liturgical_schedules ls
                    JOIN venues v ON v.venue_id = ls.venue_id
                    SET ls.location = COALESCE(v.location, v.name)
                    WHERE ls.location IS NULL AND ls.venue_id IS NOT NULL
                ");
            } catch (\Throwable $e) {
                // Ignore if columns are missing in some environments
            }
        }
    }

    public function down(): void
    {
        // Safe rollback: clear only values that exactly match the current venue's location/name
        if (Schema::hasTable('liturgical_schedules') && Schema::hasTable('venues')) {
            try {
                DB::statement("
                    UPDATE liturgical_schedules ls
                    JOIN venues v ON v.venue_id = ls.venue_id
                    SET ls.location = NULL
                    WHERE ls.location = COALESCE(v.location, v.name)
                ");
            } catch (\Throwable $e) {
                // Best-effort only
            }
        }
    }
};
