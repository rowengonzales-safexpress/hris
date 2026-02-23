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
        Schema::table('notifications', function (Blueprint $table) {
            // Add app_id column to filter notifications by specific app
            $table->unsignedBigInteger('app_id')->nullable()->after('user_id');

            // Add user_to column for sitehead_user_id targeting
            $table->unsignedBigInteger('user_to')->nullable()->after('app_id');

            // Add indexes for better query performance
            $table->index(['app_id', 'user_to']);
            $table->index(['user_id', 'app_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['app_id']);
            $table->dropForeign(['user_to']);

            // Drop indexes
            $table->dropIndex(['app_id', 'user_to']);
            $table->dropIndex(['user_id', 'app_id']);

            // Drop columns
            $table->dropColumn(['app_id', 'user_to']);
        });
    }
};
