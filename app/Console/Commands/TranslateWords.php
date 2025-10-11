<?php

namespace App\Console\Commands;

use App\Models\Word;
use App\Repositories\WordRepository;
use App\Services\OpenRouterService;
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
    protected $description = 'Command description';

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
                $this->warn("No words found in the JSON file.");
                return CommandAlias::SUCCESS;
            }

            $words = collect($words)->filter(function ($word) {
                return !Word::query()->where('word', $word)->exists();
            });

            // Use Laravel's collection for better data manipulation
            $wordChunks = $words->chunk(50);
            $totalChunks = $wordChunks->count();
            $totalSavedWords = 0;

            $this->info("Processing {$totalChunks} chunks of words...");

            // Create progress bar with Laravel's built-in method
            $progressBar = $this->output->createProgressBar($totalChunks);
            $progressBar->start();

            $openRouterService = app(OpenRouterService::class);
            $wordRepository = app(WordRepository::class);

            $wordChunks->each(function ($chunk, $index) use ($openRouterService, $wordRepository, $model, &$totalSavedWords, $progressBar, $totalChunks) {
                $yamlResponse = $openRouterService->translateWords($chunk->toArray(), $model);
                $savedWords = $wordRepository->saveFromYaml($yamlResponse);

                $totalSavedWords += count($savedWords);
                $chunkNumber = $index + 1;

                $this->line(" Chunk {$chunkNumber}/{$totalChunks} processed - " . $chunk->count() . " words");
                $progressBar->advance();
            });

            $progressBar->finish();
            $this->newLine();
            $this->info("Translation completed! Total words saved: {$totalSavedWords}");

            return CommandAlias::SUCCESS;

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return CommandAlias::FAILURE;
        }
    }
}
