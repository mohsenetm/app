<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('word')->unique();
            $table->string('phonetic')->nullable();
            $table->json('synonyms')->nullable();
            $table->json('antonyms')->nullable();
            $table->text('notes')->nullable();
            $table->longText('raw_yaml')->nullable();
            $table->timestamps();

            $table->index('word');
        });

        Schema::create('word_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained()->onDelete('cascade');
            $table->text('meaning_en');
            $table->text('meaning_fa');
            $table->string('part_of_speech', 50)->nullable();
            $table->timestamps();

            $table->index('word_id');
        });

        Schema::create('word_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('definition_id')
                ->constrained('word_definitions')
                ->onDelete('cascade');
            $table->text('example_en');
            $table->text('example_fa');
            $table->timestamps();

            $table->index('definition_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('word_examples');
        Schema::dropIfExists('word_definitions');
        Schema::dropIfExists('words');
    }
};
