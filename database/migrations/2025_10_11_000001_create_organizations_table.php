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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id('org_id');
            $table->foreignId('adviser_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('org_name', [
                'Himig Diwa Chorale',
                'Acolytes and Lectors',
                'Children of Mary',
                'Student Catholic Action',
                'Young Missionaries Club',
                'Catechetical Organization',
            ])->unique();
            $table->string('org_type', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
