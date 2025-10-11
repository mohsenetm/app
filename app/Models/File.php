<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'season',
        'episode',
        'total_words_scanned',
        'valid_dictionary_words',
        'invalid_words',
        'unique_words',
    ];

    protected $casts = [
        'season' => 'integer',
        'episode' => 'integer',
        'total_words_scanned' => 'integer',
        'valid_dictionary_words' => 'integer',
        'invalid_words' => 'integer',
        'unique_words' => 'integer',
    ];

    /**
     * رابطه با کلمات (از طریق جدول واسط)
     */
    public function words(): BelongsToMany
    {
        return $this->belongsToMany(Word::class, 'word_file_occurrences')
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
     * گرفتن کلمات پرتکرار این فایل
     */
    public function topWords(int $limit = 10)
    {
        return $this->words()
            ->orderByPivot('count', 'desc')
            ->limit($limit)
            ->get();
    }
}
