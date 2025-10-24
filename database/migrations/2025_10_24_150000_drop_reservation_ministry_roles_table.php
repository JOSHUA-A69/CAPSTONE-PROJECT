<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('reservation_ministry_roles');
    }

    public function down(): void
    {
        // Irreversible drop; use earlier migration to recreate if ever needed
    }
};
