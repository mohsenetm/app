<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardReview;
use App\Models\ReviewLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CardReviewSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // دریافت کارت‌های دسته‌های کاربر
            $cards = Card::whereHas('deck', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->get();

            foreach ($cards as $card) {
                // 70% کارت‌ها دارای وضعیت مرور باشند
                if (rand(1, 100) <= 70) {
                    $this->createReviewForCard($card, $user);
                }
            }
        }
    }

    private function createReviewForCard($card, $user)
    {
        $statuses = ['new', 'learning', 'review', 'relearning'];
        $weights = [20, 25, 50, 5]; // احتمال هر وضعیت

        $status = $this->weightedRandom($statuses, $weights);

        $review = CardReview::create([
            'card_id' => $card->id,
            'user_id' => $user->id,
            'status' => $status,
            'ease_factor' => $this->getEaseFactor($status),
            'interval' => $this->getInterval($status),
            'repetitions' => $this->getRepetitions($status),
            'due_date' => $this->getDueDate($status),
            'last_reviewed_at' => $this->getLastReviewedAt($status),
            'review_count' => $this->getReviewCount($status),
            'lapses' => $this->getLapses($status),
        ]);

        // ایجاد تاریخچه مرور
        $this->createReviewLogs($review);
    }

    private function getEaseFactor($status)
    {
        switch ($status) {
            case 'new':
                return 2.5;
            case 'learning':
                return rand(20, 28) / 10; // 2.0 - 2.8
            case 'review':
                return rand(18, 35) / 10; // 1.8 - 3.5
            case 'relearning':
                return rand(13, 20) / 10; // 1.3 - 2.0
        }
    }

    private function getInterval($status)
    {
        switch ($status) {
            case 'new':
                return 0;
            case 'learning':
                return rand(1, 4);
            case 'review':
                return rand(5, 180); // 5-180 روز
            case 'relearning':
                return rand(1, 7);
        }
    }

    private function getRepetitions($status)
    {
        switch ($status) {
            case 'new':
                return 0;
            case 'learning':
                return rand(1, 3);
            case 'review':
                return rand(4, 20);
            case 'relearning':
                return rand(0, 2);
        }
    }

    private function getDueDate($status)
    {
        switch ($status) {
            case 'new':
                return now();
            case 'learning':
                return Carbon::now()->addHours(rand(1, 24));
            case 'review':
                $daysAgo = rand(-30, 30); // 30 روز قبل تا 30 روز آینده
                return Carbon::now()->addDays($daysAgo);
            case 'relearning':
                return Carbon::now()->addDays(rand(0, 3));
        }
    }

    private function getLastReviewedAt($status)
    {
        if ($status === 'new') {
            return null;
        }

        return Carbon::now()->subDays(rand(1, 30));
    }

    private function getReviewCount($status)
    {
        switch ($status) {
            case 'new':
                return 0;
            case 'learning':
                return rand(1, 5);
            case 'review':
                return rand(5, 50);
            case 'relearning':
                return rand(10, 30);
        }
    }

    private function getLapses($status)
    {
        switch ($status) {
            case 'new':
            case 'learning':
                return 0;
            case 'review':
                return rand(0, 3);
            case 'relearning':
                return rand(1, 5);
        }
    }

    private function createReviewLogs($review)
    {
        $count = min($review->review_count, 10); // حداکثر 10 لاگ برای هر کارت

        for ($i = 0; $i < $count; $i++) {
            $ratings = ['again', 'hard', 'good', 'easy'];
            $weights = [10, 20, 50, 20];
            $rating = $this->weightedRandom($ratings, $weights);

            ReviewLog::create([
                'card_id' => $review->card_id,
                'user_id' => $review->user_id,
                'rating' => $rating,
                'time_taken' => rand(2, 30), // 2-30 ثانیه
                'ease_factor_before' => rand(13, 30) / 10,
                'ease_factor_after' => rand(13, 30) / 10,
                'interval_before' => rand(1, 100),
                'interval_after' => rand(1, 150),
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }
    }

    private function weightedRandom($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);

        $currentWeight = 0;
        foreach ($items as $index => $item) {
            $currentWeight += $weights[$index];
            if ($random <= $currentWeight) {
                return $item;
            }
        }

        return $items[0];
    }
}
