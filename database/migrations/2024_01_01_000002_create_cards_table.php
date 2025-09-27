<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->unique()->nullable();
            $table->string('content_md5')->index()->nullable();
            $table->foreignId('deck_id')->constrained()->onDelete('cascade');
            $table->text('front'); // سوال یا روی کارت
            $table->text('back'); // جواب یا پشت کارت
            $table->text('notes')->nullable(); // یادداشت‌های اضافی
            $table->enum('type', ['basic', 'reverse', 'cloze'])->default('basic');
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cards');
    }
}
