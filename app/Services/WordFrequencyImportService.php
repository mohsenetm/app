<?php

namespace App\Services;

use App\Models\Word;
use App\Models\File;
use App\Models\WordFileOccurrence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WordFrequencyImportService
{
    /**
     * Import کردن فایل JSON
     */
    public function importFromJson(string $jsonPath): array
    {
        if (!file_exists($jsonPath)) {
            throw new \Exception("File not found: {$jsonPath}");
        }

        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON: " . json_last_error_msg());
        }

        return $this->processData($data);
    }

    /**
     * پردازش داده‌ها
     */
    private function processData(array $data): array
    {
        $stats = [
            'files_processed' => 0,
            'words_created' => 0,
            'occurrences_created' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($data['files'] ?? [] as $fileName => $fileData) {
                $this->processFile($fileName, $fileData, $stats);
                $stats['files_processed']++;
            }

            DB::commit();

            Log::info('Word frequency import completed', $stats);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Word frequency import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return $stats;
    }

    /**
     * پردازش یک فایل
     */
    private function processFile(string $fileName, array $fileData, array &$stats): void
    {
        // استخراج شماره فصل و قسمت از نام فایل
        $seasonEpisode = $this->extractSeasonEpisode($fileName);

        // ایجاد یا به‌روزرسانی فایل
        $file = File::updateOrCreate(
            ['file_path' => $fileData['file_path']],
            [
                'file_name' => $fileName,
                'season' => $seasonEpisode['season'],
                'episode' => $seasonEpisode['episode'],
                'total_words_scanned' => $fileData['total_words_scanned'],
                'valid_dictionary_words' => $fileData['valid_dictionary_words'],
                'invalid_words' => $fileData['invalid_words'],
                'unique_words' => $fileData['unique_words'],
            ]
        );

        // پردازش کلمات
        foreach ($fileData['word_frequency'] ?? [] as $wordText => $wordData) {
            try {
                $this->processWord($file, $wordText, $wordData, $stats);
            } catch (\Exception $e) {
                $stats['errors'][] = [
                    'file' => $fileName,
                    'word' => $wordText,
                    'error' => $e->getMessage(),
                ];
                Log::warning('Failed to process word', [
                    'file' => $fileName,
                    'word' => $wordText,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * پردازش یک کلمه
     */
    private function processWord(File $file, string $wordText, array $wordData, array &$stats): void
    {
        // ایجاد یا یافتن کلمه
        $word = Word::firstOrCreate(['word' => strtolower($wordText)]);

        if ($word->wasRecentlyCreated) {
            $stats['words_created']++;
        }

        // ایجاد یا به‌روزرسانی occurrence
        $occurrence = WordFileOccurrence::updateOrCreate(
            [
                'word_id' => $word->id,
                'file_id' => $file->id,
            ],
            [
                'count' => $wordData['count'],
                'percentage' => $wordData['percentage'],
                'cumulative_count' => $wordData['cumulative_count'],
                'cumulative_percentage' => $wordData['cumulative_percentage'],
            ]
        );

        if ($occurrence->wasRecentlyCreated) {
            $stats['occurrences_created']++;
        }
    }

    /**
     * استخراج شماره فصل و قسمت از نام فایل
     * مثال: "Friends - 1x01 - The One Where Monica Gets a Roommate"
     */
    private function extractSeasonEpisode(string $fileName): array
    {
        $result = ['season' => null, 'episode' => null];

        // الگوی "1x01" یا "S01E01"
        if (preg_match('/(\d+)x(\d+)/i', $fileName, $matches)) {
            $result['season'] = (int) $matches[1];
            $result['episode'] = (int) $matches[2];
        } elseif (preg_match('/S(\d+)E(\d+)/i', $fileName, $matches)) {
            $result['season'] = (int) $matches[1];
            $result['episode'] = (int) $matches[2];
        }

        return $result;
    }
}
