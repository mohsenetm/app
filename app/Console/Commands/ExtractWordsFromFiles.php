<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ExtractWordsFromFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:extract-words 
                            {directory : The directory path to scan}
                            {--output=word-frequency : Output file name (without extension)}
                            {--min-length=2 : Minimum word length to include}
                            {--extensions=txt,php,md : Comma-separated file extensions to process}
                            {--language=en : Dictionary language (en/fa)}
                            {--force-download : Force re-download dictionary}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract and count words from files with detailed per-file and aggregate analysis';

    private array $dictionary = [];
    private string $dictionaryPath;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directory = $this->argument('directory');
        $outputBase = $this->option('output');
        $minLength = (int) $this->option('min-length');
        $extensions = explode(',', $this->option('extensions'));
        $language = $this->option('language');
        $forceDownload = $this->option('force-download');

        // بررسی وجود دایرکتوری
        if (!File::isDirectory($directory)) {
            $this->error("Directory not found: {$directory}");
            return 1;
        }

        // دانلود و بارگذاری دیکشنری
        $this->info("Loading dictionary...");
        if (!$this->loadOrDownloadDictionary($language, $forceDownload)) {
            $this->error("Failed to load dictionary");
            return 1;
        }
        $this->info("Dictionary loaded: " . number_format(count($this->dictionary)) . " words");

        $this->info("Scanning directory: {$directory}");
        $this->info("Processing file extensions: " . implode(', ', $extensions));

        // دریافت تمام فایل‌ها
        $files = $this->getFilesRecursively($directory, $extensions);
        
        if (empty($files)) {
            $this->warn("No files found with specified extensions.");
            return 1;
        }

        $this->info("Found " . count($files) . " files to process.");

        // آماده‌سازی ساختار داده
        $perFileAnalysis = [];
        $aggregateWordCounts = [];
        $wordFileMapping = []; // برای ذخیره اینکه هر کلمه در کدام فایل‌ها آمده
        
        $totalWordsFound = 0;
        $wordsNotInDictionary = 0;
        
        $progressBar = $this->output->createProgressBar(count($files));
        $progressBar->start();

        // پردازش هر فایل
        foreach ($files as $filePath) {
            $relativePath = str_replace($directory . '/', '', $filePath);
            $content = File::get($filePath);
            $words = $this->extractWords($content, $minLength);
            
            $fileWordCounts = [];
            $fileValidWords = 0;
            $fileInvalidWords = 0;

            foreach ($words as $word) {
                $totalWordsFound++;
                
                // فقط کلماتی که در دیکشنری هستند
                if (isset($this->dictionary[$word])) {
                    $fileWordCounts[$word] = ($fileWordCounts[$word] ?? 0) + 1;
                    $aggregateWordCounts[$word] = ($aggregateWordCounts[$word] ?? 0) + 1;
                    
                    // ثبت اینکه این کلمه در این فایل آمده
                    if (!isset($wordFileMapping[$word])) {
                        $wordFileMapping[$word] = [];
                    }
                    if (!isset($wordFileMapping[$word][$relativePath])) {
                        $wordFileMapping[$word][$relativePath] = 0;
                    }
                    $wordFileMapping[$word][$relativePath]++;
                    
                    $fileValidWords++;
                } else {
                    $fileInvalidWords++;
                    $wordsNotInDictionary++;
                }
            }

            // مرتب‌سازی کلمات فایل
            arsort($fileWordCounts);

            // محاسبه آمار فایل
            $fileTotalOccurrences = array_sum($fileWordCounts);
            
            $perFileAnalysis[$relativePath] = [
                'file_path' => $relativePath,
                'total_words_scanned' => count($words),
                'valid_dictionary_words' => $fileValidWords,
                'invalid_words' => $fileInvalidWords,
                'unique_words' => count($fileWordCounts),
                'total_occurrences' => $fileTotalOccurrences,
                'word_frequency' => $this->calculateCoverageArray($fileWordCounts, $fileTotalOccurrences),
                'top_10_words' => array_slice($fileWordCounts, 0, 10, true)
            ];

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if (empty($aggregateWordCounts)) {
            $this->warn("No valid dictionary words found in the files.");
            return 1;
        }

        // مرتب‌سازی تجمعی
        arsort($aggregateWordCounts);

        // محاسبه آمار تجمعی
        $totalOccurrences = array_sum($aggregateWordCounts);
        $aggregateAnalysis = $this->calculateCoverageArray($aggregateWordCounts, $totalOccurrences);

        // اضافه کردن اطلاعات فایل‌ها به هر کلمه
        foreach ($aggregateAnalysis as $word => &$data) {
            $data['files'] = $wordFileMapping[$word];
            $data['file_count'] = count($wordFileMapping[$word]);
        }

        // ذخیره خروجی‌ها
        $this->saveResults(
            $perFileAnalysis,
            $aggregateAnalysis,
            $outputBase,
            $totalOccurrences,
            $totalWordsFound,
            $wordsNotInDictionary,
            count($files)
        );

        // نمایش خلاصه
        $this->displaySummary(
            $perFileAnalysis,
            $aggregateAnalysis,
            $totalOccurrences,
            $totalWordsFound,
            $wordsNotInDictionary,
            count($files)
        );

        return 0;
    }

    /**
     * محاسبه درصد پوشش و آمار برای آرایه
     */
    private function calculateCoverageArray(array $wordCounts, int $totalOccurrences): array
    {
        $result = [];
        $cumulativeCount = 0;

        foreach ($wordCounts as $word => $count) {
            $cumulativeCount += $count;
            $cumulativePercentage = round(($cumulativeCount / $totalOccurrences) * 100, 2);

            $result[$word] = [
                'count' => $count,
                'percentage' => round(($count / $totalOccurrences) * 100, 2),
                'cumulative_count' => $cumulativeCount,
                'cumulative_percentage' => $cumulativePercentage
            ];
        }

        return $result;
    }

    /**
     * ذخیره نتایج در فرمت‌های مختلف
     */
    private function saveResults(
        array $perFileAnalysis,
        array $aggregateAnalysis,
        string $outputBase,
        int $totalOccurrences,
        int $totalWordsFound,
        int $wordsNotInDictionary,
        int $fileCount
    ): void {
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        // 1. ذخیره JSON تجمعی با جزئیات کامل
        $aggregateJson = [
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'dictionary' => basename($this->dictionaryPath),
                'total_files_processed' => $fileCount,
                'total_words_scanned' => $totalWordsFound,
                'valid_dictionary_words' => $totalOccurrences,
                'excluded_words' => $wordsNotInDictionary,
                'exclusion_percentage' => round(($wordsNotInDictionary / $totalWordsFound) * 100, 2),
                'unique_words' => count($aggregateAnalysis)
            ],
            'statistics' => [
                'top_100_coverage' => count($aggregateAnalysis) >= 100 ? 
                    array_values(array_slice($aggregateAnalysis, 99, 1))[0]['cumulative_percentage'] : null,
                'top_500_coverage' => count($aggregateAnalysis) >= 500 ? 
                    array_values(array_slice($aggregateAnalysis, 499, 1))[0]['cumulative_percentage'] : null,
                'top_1000_coverage' => count($aggregateAnalysis) >= 1000 ? 
                    array_values(array_slice($aggregateAnalysis, 999, 1))[0]['cumulative_percentage'] : null,
            ],
            'words' => $aggregateAnalysis
        ];

        $aggregateJsonPath = storage_path("app/{$outputBase}_aggregate.json");
        File::put($aggregateJsonPath, json_encode($aggregateJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("✓ Aggregate JSON saved: {$aggregateJsonPath}");

        // 2. ذخیره JSON تحلیل هر فایل
        $perFileJson = [
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'total_files' => $fileCount
            ],
            'files' => $perFileAnalysis
        ];

        $perFileJsonPath = storage_path("app/{$outputBase}_per_file.json");
        File::put($perFileJsonPath, json_encode($perFileJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("✓ Per-file JSON saved: {$perFileJsonPath}");

        // 3. ذخیره فایل متنی تجمعی
        $this->saveAggregateTextFile($aggregateAnalysis, $outputBase, $totalOccurrences, $totalWordsFound, $wordsNotInDictionary, $fileCount);

        // 4. ذخیره فایل متنی برای هر فایل
        $this->savePerFileTextFiles($perFileAnalysis, $outputBase);

        // 5. ذخیره خلاصه آماری
        $this->saveSummaryFile($perFileAnalysis, $aggregateAnalysis, $outputBase, $totalOccurrences, $totalWordsFound, $wordsNotInDictionary, $fileCount);
    }

    /**
     * ذخیره فایل متنی تجمعی
     */
    private function saveAggregateTextFile(
        array $aggregateAnalysis,
        string $outputBase,
        int $totalOccurrences,
        int $totalWordsFound,
        int $wordsNotInDictionary,
        int $fileCount
    ): void {
        $content = "AGGREGATE WORD FREQUENCY ANALYSIS\n";
        $content .= str_repeat("=", 120) . "\n";
        $content .= "Generated: " . now()->toDateTimeString() . "\n";
        $content .= "Dictionary: " . basename($this->dictionaryPath) . "\n";
        $content .= "Files Processed: " . number_format($fileCount) . "\n";
        $content .= "Total Words Scanned: " . number_format($totalWordsFound) . "\n";
        $content .= "Valid Dictionary Words: " . number_format($totalOccurrences) . "\n";
        $content .= "Excluded Words: " . number_format($wordsNotInDictionary) . 
            " (" . round(($wordsNotInDictionary / $totalWordsFound) * 100, 2) . "%)\n";
        $content .= str_repeat("=", 120) . "\n\n";
        
        $content .= sprintf("%-30s %12s %12s %18s %22s %12s\n", 
            "Word", "Count", "Percent", "Cumulative Count", "Cumulative Coverage", "File Count");
        
        $content .= str_repeat("-", 120) . "\n";

        foreach ($aggregateAnalysis as $word => $data) {
            $content .= sprintf("%-30s %12s %11.2f%% %18s %21.2f%% %12d\n",
                $word,
                number_format($data['count']),
                $data['percentage'],
                number_format($data['cumulative_count']),
                $data['cumulative_percentage'],
                $data['file_count']
            );
        }

        $filePath = storage_path("app/{$outputBase}_aggregate.txt");
        File::put($filePath, $content);
        $this->info("✓ Aggregate text file saved: {$filePath}");
    }

    /**
     * ذخیره فایل‌های متنی برای هر فایل
     */
    private function savePerFileTextFiles(array $perFileAnalysis, string $outputBase): void
    {
        $directory = storage_path("app/{$outputBase}_per_file");
        
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        foreach ($perFileAnalysis as $fileData) {
            $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileData['file_path']);
            $content = "WORD FREQUENCY ANALYSIS\n";
            $content .= str_repeat("=", 100) . "\n";
            $content .= "File: {$fileData['file_path']}\n";
            $content .= "Total Words Scanned: " . number_format($fileData['total_words_scanned']) . "\n";
            $content .= "Valid Dictionary Words: " . number_format($fileData['valid_dictionary_words']) . "\n";
            $content .= "Unique Words: " . number_format($fileData['unique_words']) . "\n";
            $content .= str_repeat("=", 100) . "\n\n";
            
            $content .= sprintf("%-35s %12s %12s %18s %22s\n", 
                "Word", "Count", "Percent", "Cumulative Count", "Cumulative Coverage");
            
            $content .= str_repeat("-", 100) . "\n";

            foreach ($fileData['word_frequency'] as $word => $data) {
                $content .= sprintf("%-35s %12s %11.2f%% %18s %21.2f%%\n",
                    $word,
                    number_format($data['count']),
                    $data['percentage'],
                    number_format($data['cumulative_count']),
                    $data['cumulative_percentage']
                );
            }

            $filePath = "{$directory}/{$safeFileName}.txt";
            File::put($filePath, $content);
        }

        $this->info("✓ Per-file text files saved in: {$directory}");
    }

    /**
     * ذخیره فایل خلاصه آماری
     */
    private function saveSummaryFile(
        array $perFileAnalysis,
        array $aggregateAnalysis,
        string $outputBase,
        int $totalOccurrences,
        int $totalWordsFound,
        int $wordsNotInDictionary,
        int $fileCount
    ): void {
        $content = "STATISTICAL SUMMARY\n";
        $content .= str_repeat("=", 100) . "\n";
        $content .= "Generated: " . now()->toDateTimeString() . "\n\n";

        $content .= "OVERALL STATISTICS:\n";
        $content .= str_repeat("-", 100) . "\n";
        $content .= sprintf("%-40s: %s\n", "Total Files Processed", number_format($fileCount));
        $content .= sprintf("%-40s: %s\n", "Total Words Scanned", number_format($totalWordsFound));
        $content .= sprintf("%-40s: %s\n", "Valid Dictionary Words", number_format($totalOccurrences));
        $content .= sprintf("%-40s: %s\n", "Unique Words (Aggregate)", number_format(count($aggregateAnalysis)));
        $content .= sprintf("%-40s: %s (%.2f%%)\n", "Excluded Words", number_format($wordsNotInDictionary), 
            round(($wordsNotInDictionary / $totalWordsFound) * 100, 2));
        $content .= "\n";

        $content .= "COVERAGE ANALYSIS:\n";
        $content .= str_repeat("-", 100) . "\n";
        $content .= sprintf("%-40s: %s\n", "Coverage with Top 100 Words", 
            count($aggregateAnalysis) >= 100 ? 
                array_values(array_slice($aggregateAnalysis, 99, 1))[0]['cumulative_percentage'] . "%" : "N/A");
        $content .= sprintf("%-40s: %s\n", "Coverage with Top 500 Words", 
            count($aggregateAnalysis) >= 500 ? 
                array_values(array_slice($aggregateAnalysis, 499, 1))[0]['cumulative_percentage'] . "%" : "N/A");
        $content .= sprintf("%-40s: %s\n", "Coverage with Top 1000 Words", 
            count($aggregateAnalysis) >= 1000 ? 
                array_values(array_slice($aggregateAnalysis, 999, 1))[0]['cumulative_percentage'] . "%" : "N/A");
        $content .= "\n";

        $content .= "PER-FILE STATISTICS:\n";
        $content .= str_repeat("-", 100) . "\n";
        $content .= sprintf("%-50s %15s %15s %15s\n", "File", "Total Words", "Valid Words", "Unique Words");
        $content .= str_repeat("-", 100) . "\n";

        foreach ($perFileAnalysis as $fileData) {
            $content .= sprintf("%-50s %15s %15s %15s\n",
                substr($fileData['file_path'], 0, 50),
                number_format($fileData['total_words_scanned']),
                number_format($fileData['valid_dictionary_words']),
                number_format($fileData['unique_words'])
            );
        }

        $filePath = storage_path("app/{$outputBase}_summary.txt");
        File::put($filePath, $content);
        $this->info("✓ Summary file saved: {$filePath}");
    }

    /**
     * نمایش خلاصه در کنسول
     */
    private function displaySummary(
        array $perFileAnalysis,
        array $aggregateAnalysis,
        int $totalOccurrences,
        int $totalWordsFound,
        int $wordsNotInDictionary,
        int $fileCount
    ): void {
        $this->newLine();
        $this->info("═══════════════════════════════════════════════════════════════");
        $this->info("                    ANALYSIS COMPLETE                          ");
        $this->info("═══════════════════════════════════════════════════════════════");
        $this->newLine();

        $this->info("📊 Overall Statistics:");
        $this->line("   • Files Processed: " . number_format($fileCount));
        $this->line("   • Total Words Scanned: " . number_format($totalWordsFound));
        $this->line("   • Valid Dictionary Words: " . number_format($totalOccurrences));
        $this->line("   • Unique Words: " . number_format(count($aggregateAnalysis)));
        $this->line("   • Excluded Words: " . number_format($wordsNotInDictionary) . 
            " (" . round(($wordsNotInDictionary / $totalWordsFound) * 100, 2) . "%)");

        $this->newLine();
        $this->info("📈 Top 20 Most Frequent Words (Aggregate):");
        
        $tableData = array_slice(
            array_map(function($word, $data) {
                return [
                    $word,
                    number_format($data['count']),
                    $data['percentage'] . '%',
                    $data['cumulative_percentage'] . '%',
                    $data['file_count']
                ];
            }, 
                array_keys($aggregateAnalysis), 
                array_values($aggregateAnalysis)
            ), 
            0, 
            20
        );

        $headers = ['Word', 'Count', '%', 'Cumulative %', 'Files'];
        $this->table($headers, $tableData);
    }

    /**
     * دانلود یا بارگذاری دیکشنری
     */
    private function loadOrDownloadDictionary(string $language, bool $forceDownload): bool
    {
        $this->dictionaryPath = storage_path("app/dictionaries/{$language}-dictionary.txt");

        if (File::exists($this->dictionaryPath) && !$forceDownload) {
            return $this->loadDictionaryFromFile();
        }

        $dictionaryDir = storage_path('app/dictionaries');
        if (!File::isDirectory($dictionaryDir)) {
            File::makeDirectory($dictionaryDir, 0755, true);
        }

        $this->info("Downloading dictionary for language: {$language}");
        
        try {
            if ($language === 'en') {
                return $this->downloadEnglishDictionary();
            } elseif ($language === 'fa') {
                return $this->downloadPersianDictionary();
            } else {
                $this->error("Unsupported language: {$language}");
                return false;
            }
        } catch (\Exception $e) {
            $this->error("Error downloading dictionary: " . $e->getMessage());
            return false;
        }
    }

    private function downloadEnglishDictionary(): bool
    {
        $urls = [
            'https://raw.githubusercontent.com/dwyl/english-words/master/words_alpha.txt',
            'https://raw.githubusercontent.com/first20hours/google-10000-english/master/google-10000-english-usa.txt'
        ];

        foreach ($urls as $index => $url) {
            try {
                $this->info("Trying source " . ($index + 1) . "...");
                $response = Http::timeout(60)->get($url);
                
                if ($response->successful()) {
                    $content = $response->body();
                    File::put($this->dictionaryPath, $content);
                    $this->info("Dictionary downloaded successfully!");
                    return $this->loadDictionaryFromFile();
                }
            } catch (\Exception $e) {
                $this->warn("Source " . ($index + 1) . " failed: " . $e->getMessage());
                continue;
            }
        }

        return false;
    }

    private function downloadPersianDictionary(): bool
    {
        $urls = [
            'https://raw.githubusercontent.com/persian-tools/persian-words/main/words.txt',
            'https://raw.githubusercontent.com/MrHTZ/persian-wordlist/master/persian-wordlist.txt'
        ];

        foreach ($urls as $index => $url) {
            try {
                $this->info("Trying Persian source " . ($index + 1) . "...");
                $response = Http::timeout(60)->get($url);
                
                if ($response->successful()) {
                    $content = $response->body();
                    File::put($this->dictionaryPath, $content);
                    $this->info("Persian dictionary downloaded successfully!");
                    return $this->loadDictionaryFromFile();
                }
            } catch (\Exception $e) {
                $this->warn("Persian source " . ($index + 1) . " failed: " . $e->getMessage());
                continue;
            }
        }

        return false;
    }

    private function loadDictionaryFromFile(): bool
    {
        if (!File::exists($this->dictionaryPath)) {
            return false;
        }

        $content = File::get($this->dictionaryPath);
        $words = preg_split('/\r\n|\r|\n/', $content);
        
        foreach ($words as $word) {
            $word = trim(mb_strtolower($word, 'UTF-8'));
            if (!empty($word) && mb_strlen($word, 'UTF-8') >= 2) {
                $this->dictionary[$word] = true;
            }
        }

        return count($this->dictionary) > 0;
    }

    private function getFilesRecursively(string $directory, array $extensions): array
    {
        $files = [];
        $allFiles = File::allFiles($directory);

        foreach ($allFiles as $file) {
            $extension = $file->getExtension();
            if (in_array($extension, $extensions)) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function extractWords(string $content, int $minLength): array
    {
        $content = strip_tags($content);
        $content = mb_strtolower($content, 'UTF-8');
        preg_match_all('/[\p{L}]+/u', $content, $matches);
        
        $words = $matches[0];
        
        $words = array_filter($words, function($word) use ($minLength) {
            return mb_strlen($word, 'UTF-8') >= $minLength;
        });

        return $words;
    }
}