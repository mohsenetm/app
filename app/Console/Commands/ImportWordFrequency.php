<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WordFrequencyImportService;

class ImportWordFrequency extends Command
{
    protected $signature = 'words:import {json_file}';
    protected $description = 'Import word frequency data from JSON file';

    public function handle(WordFrequencyImportService $service): int
    {
        $jsonFile = $this->argument('json_file');

        $this->info("Starting import from: {$jsonFile}");
        $this->newLine();

        try {
            $stats = $service->importFromJson($jsonFile);

            $this->info('✅ Import completed successfully!');
            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Files Processed', $stats['files_processed']],
                    ['Words Created', $stats['words_created']],
                    ['Occurrences Created', $stats['occurrences_created']],
                    ['Errors', count($stats['errors'])],
                ]
            );

            if (!empty($stats['errors'])) {
                $this->newLine();
                $this->warn('⚠️  Errors occurred:');
                foreach ($stats['errors'] as $error) {
                    $this->line("  • {$error['file']} - {$error['word']}: {$error['error']}");
                }
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Import failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
