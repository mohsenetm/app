<?php
// app/Repositories/WordRepository.php

namespace App\Repositories;

use App\Models\Word;
use App\Models\WordDefinition;
use App\Models\WordExample;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

class WordRepository
{
    /**
     * ذخیره کلمات از YAML
     */
    public function saveFromYaml(string $yamlContent): array
    {
        // حذف markdown code blocks
        $yamlContent = preg_replace('/```ya?ml\s*\n/', '', $yamlContent);
        $yamlContent = preg_replace('/```\s*$/', '', $yamlContent);
        $yamlContent = trim($yamlContent);

        try {
            $data = Yaml::parse($yamlContent);
        } catch (\Exception $e) {
            throw new \Exception("Failed to parse YAML: {$e->getMessage()}");
        }

        if (!isset($data['words']) || !is_array($data['words'])) {
            throw new \Exception('Invalid YAML structure: missing "words" key');
        }

        $savedWords = [];

        DB::beginTransaction();
        try {
            foreach ($data['words'] as $wordData) {
                $word = $this->saveWord($wordData, $yamlContent);
                $savedWords[] = [
                    'id' => $word->id,
                    'word' => $word->word,
                ];
            }
            DB::commit();
            return $savedWords;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * ذخیره یک کلمه
     */
    private function saveWord(array $wordData, string $rawYaml): Word
    {
        // پیدا کردن یا ایجاد کلمه
        $word = Word::updateOrCreate(
            ['word' => $wordData['word']],
            [
                'phonetic' => $wordData['phonetic'] ?? null,
                'synonyms' => $wordData['synonyms'] ?? [],
                'antonyms' => $wordData['antonyms'] ?? [],
                'notes' => $wordData['notes'] ?? null,
                'raw_yaml' => $rawYaml,
            ]
        );

        // حذف تعاریف قبلی
        $word->definitions()->delete();

        // ذخیره تعاریف جدید
        if (isset($wordData['definitions']) && is_array($wordData['definitions'])) {
            foreach ($wordData['definitions'] as $definitionData) {
                $this->saveDefinition($word, $definitionData);
            }
        }

        return $word;
    }

    /**
     * ذخیره یک تعریف
     */
    private function saveDefinition(Word $word, array $definitionData): void
    {
        $definition = WordDefinition::create([
            'word_id' => $word->id,
            'meaning_en' => $definitionData['meaning_en'],
            'meaning_fa' => $definitionData['meaning_fa'],
            'part_of_speech' => $definitionData['part_of_speech'] ?? null,
        ]);

        // ذخیره مثال‌ها
        if (isset($definitionData['examples']) && is_array($definitionData['examples'])) {
            foreach ($definitionData['examples'] as $exampleData) {
                WordExample::create([
                    'definition_id' => $definition->id,
                    'example_en' => $exampleData['en'],
                    'example_fa' => $exampleData['fa'],
                ]);
            }
        }
    }

    /**
     * دریافت کلمه با تمام روابط
     */
    public function getWord(string $word): ?Word
    {
        return Word::with(['definitions.examples'])
            ->where('word', $word)
            ->first();
    }

    /**
     * دریافت لیست تمام کلمات
     */
    public function getAllWords(int $perPage = 50)
    {
        return Word::with(['definitions.examples'])
            ->paginate($perPage);
    }

    /**
     * جستجوی کلمات
     */
    public function searchWords(string $query, int $perPage = 20)
    {
        return Word::where('word', 'LIKE', "%{$query}%")
            ->orWhere('notes', 'LIKE', "%{$query}%")
            ->with(['definitions.examples'])
            ->paginate($perPage);
    }
}
