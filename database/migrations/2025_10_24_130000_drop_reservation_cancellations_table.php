<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('reservation_cancellations');
    }

    public function down(): void
    {
        // No-op: table removal is irreversible in this simplified flow
    }
};
