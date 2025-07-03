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
        Schema::create('user_badges', function (Blueprint $table) {
               $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('badge_id')->constrained()->onDelete('cascade');
        $table->timestamp('earned_at')->useCurrent();
        $table->foreignId('challenge_id')->nullable()->constrained('reading_challenges')->onDelete('set null');
        
        $table->primary(['user_id', 'badge_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
};
