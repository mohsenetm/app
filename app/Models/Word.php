<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'phonetic',
        'synonyms',
        'antonyms',
        'notes',
        'raw_yaml',
    ];

    protected $casts = [
        'synonyms' => 'array',
        'antonyms' => 'array',
    ];

    /**
     * رابطه با تعاریف
     */
    public function definitions(): HasMany
    {
        return $this->hasMany(WordDefinition::class);
    }

    /**
     * دریافت کامل اطلاعات کلمه با تعاریف و مثال‌ها
     */
    public function getFullData(): array
    {
        return [
            'word' => $this->word,
            'phonetic' => $this->phonetic,
            'synonyms' => $this->synonyms ?? [],
            'antonyms' => $this->antonyms ?? [],
            'notes' => $this->notes,
            'definitions' => $this->definitions->map(function ($definition) {
                return [
                    'meaning_en' => $definition->meaning_en,
                    'meaning_fa' => $definition->meaning_fa,
                    'part_of_speech' => $definition->part_of_speech,
                    'examples' => $definition->examples->map(function ($example) {
                        return [
                            'en' => $example->example_en,
                            'fa' => $example->example_fa,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * رابطه با فایل‌ها (از طریق جدول واسط)
     */
    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'word_file_occurrences')
            ->withPivot(['count', 'percentage', 'cumulative_count', 'cumulative_percentage'])
            ->withTimestamps();
    }

    /**
     * رابطه مستقیم با جدول occurrences
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(WordFileOccurrence::class);
    }

    /**
     * تعداد کل تکرار این کلمه در تمام فایل‌ها
     */
    public function getTotalOccurrencesAttribute(): int
    {
        return $this->occurrences()->sum('count');
    }

    /**
     * تعداد فایل‌هایی که این کلمه در آن‌ها وجود دارد
     */
    public function getFileCountAttribute(): int
    {
        return $this->files()->count();
    }
}
