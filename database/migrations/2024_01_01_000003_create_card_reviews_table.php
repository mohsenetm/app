<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('card_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // وضعیت کارت
            $table->enum('status', ['new', 'learning', 'review', 'relearning'])->default('new');
            
            // فاکتورهای الگوریتم SM-2
            $table->float('ease_factor')->default(2.5); // ضریب آسانی (2.5 پیش‌فرض)
            $table->integer('interval')->default(0); // فاصله تا مرور بعدی (روز)
            $table->integer('repetitions')->default(0); // تعداد مرورهای موفق
            
            // زمان‌بندی
            $table->timestamp('due_date')->nullable(); // زمان مرور بعدی
            $table->timestamp('last_reviewed_at')->nullable();
            
            // آمار
            $table->integer('review_count')->default(0);
            $table->integer('lapses')->default(0); // تعداد فراموشی‌ها
            
            $table->timestamps();
            $table->unique(['card_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('card_reviews');
    }
}