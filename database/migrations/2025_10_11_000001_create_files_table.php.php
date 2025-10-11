<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name', 500);
            $table->string('file_path', 1000)->unique();
            $table->unsignedTinyInteger('season')->nullable();
            $table->unsignedTinyInteger('episode')->nullable();
            $table->unsignedInteger('total_words_scanned')->default(0);
            $table->unsignedInteger('valid_dictionary_words')->default(0);
            $table->unsignedInteger('invalid_words')->default(0);
            $table->unsignedInteger('unique_words')->default(0);
            $table->timestamps();

            $table->index(['season', 'episode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
