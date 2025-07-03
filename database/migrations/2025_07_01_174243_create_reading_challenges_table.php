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
        Schema::create('reading_challenges', function (Blueprint $table) {
            $table->id();
        $table->string('name', 100);
        $table->string('slug', 120)->unique();
        $table->text('description')->nullable();
        $table->date('start_date');
        $table->date('end_date');
        $table->unsignedInteger('target_count');
        $table->foreignId('badge_id')->constrained()->onDelete('cascade');
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_challenges');
    }
};
