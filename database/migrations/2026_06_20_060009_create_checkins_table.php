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
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('roadmap_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('block_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('minutes_spent')->default(0);
            $table->text('note')->nullable();
            $table->string('checkin_type')->default('study'); // study|project|community|task
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['sector_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
