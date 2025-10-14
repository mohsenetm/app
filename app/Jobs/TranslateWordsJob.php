<?php

namespace App\Jobs;

use App\Repositories\WordRepository;
use App\Services\OpenRouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateWordsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The chunk of words to translate.
     *
     * @var array
     */
    public $chunk;

    /**
     * The AI model to use for translation.
     *
     * @var string
     */
    public $model;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param array $chunk
     * @param string $model
     */
    public function __construct(array $chunk, string $model)
    {
        $this->chunk = $chunk;
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @param OpenRouterService $openRouterService
     * @param WordRepository $wordRepository
     * @return void
     */
    public function handle(OpenRouterService $openRouterService, WordRepository $wordRepository)
    {
        try {
            $yamlResponse = $openRouterService->translateWords($this->chunk, $this->model);
            $savedWords = $wordRepository->saveFromYaml($yamlResponse);

            Log::info('TranslateWordsJob processed successfully', [
                'chunk_size' => count($this->chunk),
                'saved_words' => count($savedWords),
                'model' => $this->model,
            ]);
        } catch (\Exception $e) {
            Log::error('TranslateWordsJob failed', [
                'chunk_size' => count($this->chunk),
                'model' => $this->model,
                'error' => $e->getMessage(),
            ]);
            // Re-throw the exception to let the queue system handle it (e.g., retry or mark as failed)
            throw $e;
        }
    }
}
