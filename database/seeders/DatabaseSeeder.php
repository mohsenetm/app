<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\CardReview;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DeckSeeder::class,
            CardSeeder::class,
            CardReviewSeeder::class,
            StatisticsSeeder::class
        ]);
    }
}
