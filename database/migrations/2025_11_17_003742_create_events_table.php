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
            $table->foreignId('calendar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->uuid('series_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('images')->nullable(); // Array of image URLs
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('is_all_day')->default(false);
            $table->string('location')->nullable();
            $table->string('url')->nullable();
            $table->enum('repeat_frequency', ['none', 'daily', 'weekly', 'monthly', 'yearly'])->default('none');
            $table->date('repeat_end_date')->nullable(); // When to stop repeating
            $table->string('visibility_rule')->nullable(); // e.g., 'girls_only', 'within_20km'
            $table->integer('max_distance_km')->nullable(); // Max distance for visibility
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('opt_in_enabled')->default(false);
            $table->timestamps();

            $table->index(['calendar_id', 'start_date']);
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
