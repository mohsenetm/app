<?php

namespace App\Console\Commands;

use App\Jobs\TranslateWordsJob;
use App\Models\Word;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as CommandAlias;

class TranslateWords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:translate-words';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads a JSON file of words, filters existing ones, and dispatches jobs to translate and save them in parallel.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Use Laravel's Storage facade to read the file
            if (!Storage::disk('local')->exists('words/words.json')) {
                $this->error("JSON file not found at: words/words.json");
                return CommandAlias::FAILURE;
            }

            $jsonContent = Storage::disk('local')->get('words/words.json');
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error("Failed to parse JSON file: " . json_last_error_msg());
                return CommandAlias::FAILURE;
            }

            $words = $data ?? [];

            $model = 'google/gemma-3-12b-it';

            if (empty($words)) {
                $this->warn('No words found in the JSON file.');
                return CommandAlias::SUCCESS;
            }

            // Filter out words that are already in the database
            $existingWords = Word::pluck('word')->toArray();
            $newWords = array_diff($words, $existingWords);

            if (empty($newWords)) {
                $this->info('All words from the file are already in the database.');
                return CommandAlias::SUCCESS;
            }

            $this->info(count($newWords) . ' new words to process.');

            // Chunk the words to avoid hitting API limits and for parallel processing
            $wordChunks = collect($newWords)->chunk(50); // Chunk size of 50 is a reasonable default
            $totalChunks = $wordChunks->count();

            $this->info("Dispatching {$totalChunks} jobs to the queue...");

            // Dispatch a job for each chunk
            $wordChunks->each(function ($chunk) use ($model) {
                TranslateWordsJob::dispatch($chunk->toArray(), $model);
            });

            $this->info('All jobs have been dispatched. Please run `php artisan queue:work` to process them.');

            return CommandAlias::SUCCESS;

        } catch (\Exception $e) {
            $this->error('An unexpected error occurred: ' . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
