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
        Schema::create('user_challenge_books', function (Blueprint $table) {
           $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('challenge_id')->constrained('reading_challenges')->onDelete('cascade');
        $table->foreignId('book_id')->constrained()->onDelete('cascade');
        $table->string('status')->default('planned');
        $table->timestamp('started_at')->nullable();
        $table->timestamp('completed_at')->nullable();
        $table->unsignedTinyInteger('user_rating')->nullable();
        $table->text('review')->nullable();
        $table->timestamps();
        
        $table->unique(['user_id', 'challenge_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenge_books');
    }
};
