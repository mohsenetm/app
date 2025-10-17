<?php

namespace App\Console\Commands;

use App\Models\Word;
use App\Models\WordFileOccurrence;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConvertYamlToMarkdown extends Command
{
    protected $signature = 'convert:json-to-markdown';

    protected $description = 'Convert YAML vocabulary file to Markdown format';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $wordFileO = WordFileOccurrence::query()->where('file_id', 1)
            ->where('cumulative_percentage', '<', 98)
            ->pluck('word_id')->toArray();

        $words = Word::query()->whereIn('id', $wordFileO)->get();

        // Create progress bar
        $progressBar = $this->output->createProgressBar($words->count());
        $progressBar->start();

        // Ensure storage directory exists
        Storage::makeDirectory('words');

        foreach ($words as $word) {
            $content = json_decode($word->raw_json,true);

            // Generate Markdown content
            $markdown = $this->generateMarkdown($content);

            // Store in storage/app/words directory with word as filename
            $filename = $word->word . '.md';
            $outputPath = 'words/' . $filename;
            Storage::put($outputPath, $markdown);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\n✅ All files converted successfully!");

        return 0;
    }

    private function generateMarkdown($tempData)
    {
        $markdown = "";

        $markdown .= $this->formatWord($tempData);

        return $markdown;
    }

    private function formatWord($word)
    {
        $md = "";

        // عنوان کلمه
        $md .= "### **{$word['word']}**";

        // تلفظ
        if (!empty($word['phonetic'])) {
            $md .= " `{$word['phonetic']}`";
        }
        $md .= "\n\n";

        // تعاریف
        if (!empty($word['definitions'])) {
            $md .= "#### 📖 تعاریف\n\n";

            foreach ($word['definitions'] as $def) {
                // نوع کلمه
                if (!empty($def['part_of_speech'])) {
                    $md .= "**_{$def['part_of_speech']}_**\n\n";
                }

                // معنی انگلیسی و فارسی
                $md .= "- **English:** {$def['meaning_en']}\n";
                $md .= "- **فارسی:** {$def['meaning_fa']}\n\n";

                // مثال‌ها
                if (!empty($def['examples'])) {
                    $md .= "#### مثال‌ها:\n\n";
                    foreach ($def['examples'] as $example) {
                        $md .= "- 🔹 \"{$example['en']}\"\n";
                        $md .= "  - 🔸 «{$example['fa']}»\n";
                    }
                    $md .= "\n";
                }
            }
        }

        // مترادف‌ها
        if (!empty($word['synonyms'])) {
            $md .= "#### 🔄 مترادف‌ها (Synonyms)\n";
            $md .= "- " . implode(", ", $word['synonyms']) . "\n\n";
        }

        // متضادها
        if (!empty($word['antonyms'])) {
            $md .= "#### ↔️ متضادها (Antonyms)\n";
            $md .= "- " . implode(", ", $word['antonyms']) . "\n\n";
        }

        // یادداشت‌ها
        if (!empty($word['notes'])) {
            $md .= "#### 📝 یادداشت\n";
            $md .= "> {$word['notes']}\n";
        }

        return $md;
    }
}
