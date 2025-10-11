<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WordDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'word_id',
        'meaning_en',
        'meaning_fa',
        'part_of_speech',
    ];

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    public function examples(): HasMany
    {
        return $this->hasMany(WordExample::class, 'definition_id');
    }
}
