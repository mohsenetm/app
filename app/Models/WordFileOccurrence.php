<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordFileOccurrence extends Model
{
    protected $fillable = [
        'word_id',
        'file_id',
        'count',
        'percentage',
        'cumulative_count',
        'cumulative_percentage',
    ];

    protected $casts = [
        'word_id' => 'integer',
        'file_id' => 'integer',
        'count' => 'integer',
        'percentage' => 'decimal:2',
        'cumulative_count' => 'integer',
        'cumulative_percentage' => 'decimal:2',
    ];

    /**
     * رابطه با کلمه
     */
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    /**
     * رابطه با فایل
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
