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
        // Add 'banned' to approval_status enum
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing enum constraint
            // DB::statement("ALTER TABLE users DROP CONSTRAINT users_approval_status_check");

            // Modify the enum to include 'banned'
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'banned'])
                  ->default('pending')
                  ->change();

            // Add is_disabled column
            $table->boolean('is_disabled')->default(false)->after('approval_status');
            $table->timestamp('disabled_at')->nullable()->after('is_disabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove any users with banned status to avoid constraint issues
            DB::table('users')->where('approval_status', 'banned')->update(['approval_status' => 'rejected']);

            // Drop the enum constraint
            DB::statement("ALTER TABLE users DROP CONSTRAINT users_approval_status_check");

            // Revert to original enum values
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->change();

            // Drop is_disabled and disabled_at columns
            $table->dropColumn(['is_disabled', 'disabled_at']);
        });
    }
};
