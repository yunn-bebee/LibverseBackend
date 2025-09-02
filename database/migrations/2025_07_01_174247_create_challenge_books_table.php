<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_books', function (Blueprint $table) {
            $table->foreignId('reading_challenge_id')->constrained('reading_challenges')->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Update primary key to use reading_challenge_id
            $table->primary(['reading_challenge_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_books');
    }
};
