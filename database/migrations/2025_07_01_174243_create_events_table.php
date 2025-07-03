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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
        $table->string('title', 255);
        $table->string('slug', 120)->unique();
        $table->text('description');
        $table->string('event_type');
        $table->dateTime('start_time');
        $table->dateTime('end_time');
        $table->enum('location_type', ['physical', 'virtual', 'hybrid']);
        $table->text('physical_address')->nullable();
        $table->string('zoom_link')->nullable();
        $table->unsignedInteger('max_attendees')->nullable();
        $table->string('cover_image')->nullable();
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->foreignId('forum_id')->nullable()->constrained()->onDelete('set null');
        $table->timestamps();
        
        $table->index('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
