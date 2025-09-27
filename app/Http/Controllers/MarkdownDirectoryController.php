<?php

namespace App\Http\Controllers;


use App\Models\Card;
use App\Models\Deck;
use Illuminate\Support\Facades\File;
use Illuminate\Http\JsonResponse;

class MarkdownDirectoryController extends Controller
{

    private const MARKDOWN_EXTENSIONS = ['md'];

    public function getMarkdownContents(string $path): array
    {
        $deck = $this->getOrCreateDeck($path);
        $path = public_path($path);

        if ($error = $this->validateDirectory($path)) {
            return $error;
        }

        $sections = $this->extractSectionsFromDirectory($path);
        return $this->processCards($sections, $deck);
    }

    private function getOrCreateDeck(string $path): Deck
    {
        $deck = Deck::query()->where('name', $path)->first();

        if (!$deck) {
            $deck = Deck::query()->create([
                'user_id' => 1,
                'name' => $path,
                'description' => '-',
                'new_cards_per_day' => 20,
                'review_cards_per_day' => 60,
                'is_active' => rand(0, 10) > 1,
            ]);
        }

        return $deck;
    }

    private function validateDirectory(string $path): ?array
    {
        if (!File::exists($path)) {
            return [
                'error' => true,
                'message' => 'مسیر داده شده وجود ندارد.',
                'path' => $path
            ];
        }

        if (!File::isDirectory($path)) {
            return [
                'error' => true,
                'message' => 'مسیر داده شده یک دایرکتوری نیست.',
                'path' => $path
            ];
        }

        return null;
    }

    private function extractSectionsFromDirectory(string $path): array
    {
        $tempOutput = $this->scanDirectory($path);
        $files = $tempOutput['markdown_files'];
        $sections = [];

        foreach ($files as $file) {
            $sections = array_merge($file['sections'], $sections);
        }

        return $sections;
    }

    private function processCards(array $sections, Deck $deck): array
    {
        $newCards = [];
        $updateCards = [];

        foreach ($sections as $section) {
            $card = Card::query()->where([
                'identifier' => $section['identifier']
            ])->first();

            if ($card !== null && $card->content_md5 !== $section['content_md5']) {
                $card->update([
                    'content_md5' => $section['content_md5'],
                    'front' => $section['front'] ?? $section['content'],
                    'back' => $section['front'],
                ]);
                $updateCards[] = $card;
            }

            if (!$card) {
                $newCards[] = Card::query()->create([
                    'user_id' => 1,
                    'identifier' => $section['identifier'],
                    'content_md5' => $section['content_md5'],
                    'deck_id' => $deck->id,
                    'front' => $section['front'] ?? $section['content'],
                    'back' => $section['back'] ?? '-',
                ]);
            }
        }

        return ['new_cards' => $newCards, 'update_cards' => $updateCards];
    }

    private function scanDirectory(string $path): array
    {
        $contents = [
            'type' => 'directory',
            'name' => basename($path),
            'path' => $path,
            'directories' => [],
            'markdown_files' => [],
            'stats' => [
                'total_directories' => 0,
                'total_markdown_files' => 0,
                'total_size_bytes' => 0
            ]
        ];

        try {

            $items = File::allFiles($path);

            foreach ($items as $file) {
                if ($this->isMarkdownFile($file->getFilename())) {
                    $markdownData = $this->processMarkdownFile($file);
                    if ($markdownData) {
                        $contents['markdown_files'][] = $markdownData;
                    }
                }
            }

        } catch (\Exception $e) {
            $contents['error'] = true;
            $contents['message'] = 'خطا در خواندن دایرکتوری: ' . $e->getMessage();
        }

        return $contents;
    }

    private function isMarkdownFile(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($extension, self::MARKDOWN_EXTENSIONS);
    }

    private function processMarkdownFile(\SplFileInfo $file): ?array
    {
        try {
            $content = File::get($file->getPathname());

            return [
                'type' => 'markdown_file',
                'name' => $file->getFilename(),
                'name_without_extension' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
                'path' => $file->getPathname(),
                'last_modified' => date('Y-m-d H:i:s', $file->getMTime()),
                'sections' => $this->extractH3Sections($content, pathinfo($file->getFilename(), PATHINFO_FILENAME)),
                'line_count' => substr_count($content, "\n") + 1,
                'word_count' => str_word_count(strip_tags($content)),
                'character_count' => mb_strlen($content),
            ];

        } catch (\Exception $e) {
            return [
                'type' => 'markdown_file',
                'name' => $file->getFilename(),
                'path' => $file->getPathname(),
                'error' => true,
                'message' => 'خطا در خواندن فایل: ' . $e->getMessage()
            ];
        }
    }

    public function scanMarkdownDirectory(string $directoryPath = null): JsonResponse
    {
        // اگر مسیر داده نشده، از مسیر پیش‌فرض استفاده کن
        $path = $directoryPath ?: base_path('markdown-files');

        $result = $this->getMarkdownContents($path);

        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private static function parseHeader(string $line): ?array
    {
        if (preg_match('/^(#{1,6})\s+(.+?)(?:\s+#*)?$/', $line, $matches)) {
            return [
                'raw' => $line,
                'level' => strlen($matches[1]),
                'text' => trim($matches[2]),
                'type' => 'atx'
            ];
        }

        return null;
    }

    private static function processSection(array $section, bool $includeMetadata): array
    {
        // تمیز کردن محتوا
        $section['content'] = rtrim($section['content'], "\n");

        if ($includeMetadata) {
            // اضافه کردن متادیتا
            $section['metadata'] = [
                'word_count' => str_word_count(strip_tags($section['content'])),
                'character_count' => mb_strlen($section['content']),
                'line_count' => substr_count($section['content'], "\n") + 1,
            ];
        }

        return $section;
    }

    public function extractH3Sections(string $text, string $path): array
    {
        $pattern = '/### ([^\n]+)(.*?)(?=### |$)/s';
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        $sections = [];
        foreach ($matches as $match) {
            $sectionContent = $match[0];
            $title = trim($match[1]);

            // Extract front and back content using @@@ delimiter
            $contentParts = $this->extractSectionParts($sectionContent);

            $sections[] = [
                'identifier' => $this->generateIdentifier($path, $title),
                'content_md5' => md5($sectionContent),
                'title' => $title,
                'content' => $sectionContent,
                'front' => $contentParts['front'],
                'back' => $contentParts['back'],
            ];
        }

        return $sections;
    }

    private function extractSectionParts(string $sectionContent): array
    {
        $pattern = '/###(.*?)@@@(.*?)$/s';
        preg_match($pattern, $sectionContent, $matches);

        return [
            'front' => $matches[1] ?? null,
            'back' => $matches[2] ?? null,
        ];
    }

    private function generateIdentifier(string $path, string $title): string
    {
        return md5($path . $title);
    }
}
