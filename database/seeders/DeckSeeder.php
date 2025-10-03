<?php

namespace Database\Seeders;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeckSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $decks = [
            [
                'name' => 'a',
                'description' => 'واژگان پایه و اساسی زبان انگلیسی',
                'new_cards_per_day' => 20,
                'review_cards_per_day' => 100,
            ],
            [
                'name' => 'b',
                'description' => 'واژگان پیشرفته و اصطلاحات تخصصی',
                'new_cards_per_day' => 15,
                'review_cards_per_day' => 80,
            ],
            [
                'name' => 'c',
                'description' => 'مفاهیم و توابع PHP و Laravel',
                'new_cards_per_day' => 10,
                'review_cards_per_day' => 50,
            ],
            [
                'name' => 'd',
                'description' => 'مفاهیم SQL و طراحی پایگاه داده',
                'new_cards_per_day' => 10,
                'review_cards_per_day' => 50,
            ],
            [
                'name' => 'e',
                'description' => 'وقایع مهم تاریخ ایران',
                'new_cards_per_day' => 15,
                'review_cards_per_day' => 60,
            ],
            [
                'name' => 'جغرافیا',
                'description' => 'پایتخت‌ها و اطلاعات جغرافیایی کشورها',
                'new_cards_per_day' => 25,
                'review_cards_per_day' => 100,
            ],
            [
                'name' => 'ریاضیات',
                'description' => 'فرمول‌ها و مفاهیم ریاضی',
                'new_cards_per_day' => 10,
                'review_cards_per_day' => 40,
            ],
            [
                'name' => 'علوم کامپیوتر',
                'description' => 'الگوریتم‌ها و ساختمان داده',
                'new_cards_per_day' => 12,
                'review_cards_per_day' => 50,
            ],
        ];

        foreach ($users as $index => $user) {
            // هر کاربر 1-3 دسته دارد
            $userDecks = array_rand($decks, rand(1, 3));
            if (!is_array($userDecks)) {
                $userDecks = [$userDecks];
            }

            foreach ($userDecks as $deckIndex) {
                Deck::create([
                    'user_id' => $user->id,
                    'name' => $decks[$deckIndex]['name'],
                    'description' => $decks[$deckIndex]['description'],
                    'new_cards_per_day' => $decks[$deckIndex]['new_cards_per_day'],
                    'review_cards_per_day' => $decks[$deckIndex]['review_cards_per_day'],
                    'is_active' => rand(0, 10) > 1, // 90% فعال
                ]);
            }
        }
    }
}
