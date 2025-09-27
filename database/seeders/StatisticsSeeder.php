<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CardReview;
use App\Models\ReviewLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
    public function run()
    {
        // ایجاد داده‌های آماری برای 30 روز گذشته
        $users = User::all();

        foreach ($users as $user) {
            $this->createDailyStats($user);
        }
    }

    private function createDailyStats($user)
    {
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            // تعداد تصادفی مرور برای هر روز
            $reviewCount = rand(0, 50);

            if ($reviewCount > 0) {
                $reviews = CardReview::where('user_id', $user->id)
                    ->inRandomOrder()
                    ->limit($reviewCount)
                    ->get();

                foreach ($reviews as $review) {
                    // بروزرسانی تاریخ آخرین مرور
                    $review->last_reviewed_at = $date;
                    $review->save();

                    // ایجاد لاگ مرور
                    $ratings = ['again', 'hard', 'good', 'easy'];
                    $rating = $ratings[array_rand($ratings)];

                    ReviewLog::create([
                        'card_id' => $review->card_id,
                        'user_id' => $user->id,
                        'rating' => $rating,
                        'time_taken' => rand(3, 60),
                        'ease_factor_before' => $review->ease_factor,
                        'ease_factor_after' => $review->ease_factor + rand(-5, 5) / 10,
                        'interval_before' => $review->interval,
                        'interval_after' => $review->interval + rand(-5, 10),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }
    }
}
