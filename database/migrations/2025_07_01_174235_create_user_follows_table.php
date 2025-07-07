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
        Schema::create('user_follows', function (Blueprint $table) {
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('followee_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        
            $table->primary(['follower_id', 'followee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_follows');
    }
};
