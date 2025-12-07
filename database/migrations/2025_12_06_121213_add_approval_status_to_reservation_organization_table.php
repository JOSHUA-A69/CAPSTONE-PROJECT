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
        Schema::table('reservation_organization', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('notified_at');
            $table->text('rejection_reason')->nullable()->after('approval_status');
            $table->unsignedBigInteger('responded_by')->nullable()->after('rejection_reason');
            $table->timestamp('responded_at')->nullable()->after('responded_by');
            
            $table->foreign('responded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_organization', function (Blueprint $table) {
            $table->dropForeign(['responded_by']);
            $table->dropColumn(['approval_status', 'rejection_reason', 'responded_by', 'responded_at']);
        });
    }
};
