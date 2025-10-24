<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('priest_declines');
    }

    public function down(): void
    {
        // Irreversible by design; if needed, re-create via original migration
    }
};
