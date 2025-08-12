<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('books', function (Blueprint $table) {
        $table->id();
        $table->string('library_book_id', 50)->comment('BC Catalog ID');
        $table->string('isbn', 20)->nullable();
        $table->string('title', 255);
        $table->string('author', 100);
        $table->string('cover_image')->nullable();
        $table->text('description')->nullable();
       $table->json('genres')->nullable();
        $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
    $table->timestamps();

        $table->fullText(['title', 'author']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
