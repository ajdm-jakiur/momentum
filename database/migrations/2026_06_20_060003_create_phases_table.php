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
        Schema::create('phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('duration_label')->nullable();
            $table->text('description')->nullable();
            $table->text('milestone')->nullable();
            $table->boolean('milestone_confirmed')->default(false);
            $table->string('color', 9)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // Denormalized progress (0-100), recomputed by CompletionService from child blocks.
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->boolean('is_complete')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phases');
    }
};
