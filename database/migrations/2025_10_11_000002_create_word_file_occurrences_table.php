<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('word_file_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained('words')->onDelete('cascade');
            $table->foreignId('file_id')->constrained('files')->onDelete('cascade');
            $table->unsignedInteger('count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->unsignedInteger('cumulative_count')->default(0);
            $table->decimal('cumulative_percentage', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['word_id', 'file_id'], 'unique_word_file');
            $table->index('word_id');
            $table->index('file_id');
            $table->index('count');
            $table->index('percentage');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_file_occurrences');
    }
};
