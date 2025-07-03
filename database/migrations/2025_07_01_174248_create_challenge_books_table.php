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
        Schema::create('challenge_books', function (Blueprint $table) {
           $table->foreignId('challenge_id')->constrained('reading_challenges')->onDelete('cascade');
        $table->foreignId('book_id')->constrained()->onDelete('cascade');
        $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
        $table->timestamps();
        
        $table->primary(['challenge_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_books');
    }
};
