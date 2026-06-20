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
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('weeks_label')->nullable();
            $table->string('icon')->nullable();
            $table->text('pattern_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // Denormalized progress, recomputed by CompletionService on every resource/item save.
            $table->unsignedInteger('required_total')->default(0);
            $table->unsignedInteger('required_done')->default(0);
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
        Schema::dropIfExists('blocks');
    }
};
