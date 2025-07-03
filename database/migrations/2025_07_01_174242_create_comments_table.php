<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */ public function up(): void{
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->foreignId('parent_comment_id')
                  ->nullable()
                  ->constrained('comments')
                  ->onDelete('cascade');
            $table->unsignedTinyInteger('depth')->default(1);
            $table->timestamps();
        });

        // Add check constraint using raw SQL
        if (config('database.default') !== 'sqlite') {
            DB::statement('ALTER TABLE comments ADD CONSTRAINT chk_depth_range CHECK (depth BETWEEN 1 AND 5)');
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
