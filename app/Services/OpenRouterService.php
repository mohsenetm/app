<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenRouterService
{
    private string $apiKey;
    private string $baseUrl = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');

        if (empty($this->apiKey)) {
            throw new Exception('OpenRouter API key is not configured');
        }
    }

    /**
     * ارسال درخواست ترجمه به OpenRouter
     */
    public function translateWords(array $words, string $model = 'anthropic/claude-3.5-sonnet'): string
    {
        $wordList = implode(', ', $words);

        $prompt = $this->buildPrompt($wordList);

        try {
            $response = Http::timeout(360)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'Laravel Word Translation API',
                ])
                ->post($this->baseUrl, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 80000,
                ]);

            if (!$response->successful()) {
                Log::error('OpenRouter API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('OpenRouter API request failed: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new Exception('Invalid response structure from OpenRouter');
            }

            return $data['choices'][0]['message']['content'];

        } catch (Exception $e) {
            Log::error('OpenRouter Service Error', [
                'message' => $e->getMessage(),
                'words' => $words,
            ]);
            throw $e;
        }
    }

    /**
     * ساخت پرامپت برای AI
     */
    private function buildPrompt(string $wordList): string
    {
        return <<<PROMPT
Translate the following English words into Persian and return the result in YAML format. For each word, provide:

1. **word**: The original English word
2. **phonetic**: Phonetic transcription (IPA format if available)
3. **definitions**: Array of definitions, each containing:
   - **meaning_en**: English definition/explanation
   - **meaning_fa**: Persian translation of the definition
   - **part_of_speech**: (noun, verb, adjective, etc.)
   - **examples**: Array of example sentences, each with:
     - **en**: English example sentence
     - **fa**: Persian translation of the example
4. **synonyms**: List of English synonyms (if applicable)
5. **antonyms**: List of English antonyms (if applicable)
6. **notes**: Important grammatical points, usage tips, or semantic nuances about the ENGLISH word, such as:
   - Whether it's countable/uncountable
   - Common collocations
   - Formal vs informal usage
   - British vs American differences
   - Common mistakes or confusions
   - Prepositions typically used with this word
   - Any special grammatical patterns

**Requirements:**
- If a word has multiple meanings, include all common ones
- Provide at least 2 example sentences per meaning
- Keep the total explanation for each word under 24 lines
- Use proper YAML formatting with proper indentation
- Be concise but comprehensive
- Write notes in Persian (but about English grammar/usage)

**Output Format:**
```yaml
words:
  - word: "advice"
    phonetic: "/ədˈvaɪs/"
    definitions:
      - meaning_en: "An opinion or suggestion about what someone should do"
        meaning_fa: "نصیحت، توصیه، راهنمایی"
        part_of_speech: "noun"
        examples:
          - en: "She gave me some good advice about my career."
            fa: "او چند نصیحت خوب درباره شغلم به من داد."
          - en: "Let me give you a piece of advice."
            fa: "بگذار یک نصیحت به تو بکنم."
    synonyms: ["counsel", "guidance", "recommendation", "suggestion"]
    antonyms: []
    notes: "این کلمه uncountable است و نمی‌توان 'an advice' یا 'advices' گفت. برای بیان تعداد از 'a piece of advice' یا 'some advice' استفاده می‌شود. فعل آن 'advise' است که با 's' تلفظ می‌شود."
**Words to translate:**
{$wordList}

**Important:** Return ONLY valid YAML without any markdown code blocks or explanations.
PROMPT;
    }
}
