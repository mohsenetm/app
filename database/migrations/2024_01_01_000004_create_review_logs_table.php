<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewLogsTable extends Migration
{
    public function up()
    {
        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->enum('rating', ['again', 'hard', 'good', 'easy']); // نحوه پاسخ
            $table->integer('time_taken')->nullable(); // زمان پاسخ (ثانیه)
            $table->float('ease_factor_before');
            $table->float('ease_factor_after');
            $table->integer('interval_before');
            $table->integer('interval_after');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('review_logs');
    }
}