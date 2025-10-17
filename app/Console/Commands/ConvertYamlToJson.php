<?php

namespace App\Console\Commands;

use App\Models\Word;
use App\Models\WordFileOccurrence;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Storage;

class ConvertYamlToJson extends Command
{
    protected $signature = 'convert:yaml-to-json';

    protected $description = 'Convert YAML vocabulary file to Markdown format';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $words = Word::query()->orderBy('id')->get();

        $progressBar = $this->output->createProgressBar($words->count());
        $progressBar->start();

        foreach ($words as $word) {
            $word->raw_yaml = null;
            $word->save();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\n✅ All files converted successfully!");

        return 0;
    }

    /**
     * @param $words1
     * @param mixed $word
     * @return mixed
     */
    public function getDWord($words1, mixed $word)
    {
        foreach ($words1 as $dWord) {
            if ($dWord['word'] == $word->word) {
                return $dWord;
            }
        }
    }
}
