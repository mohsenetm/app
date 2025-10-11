<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordExample extends Model
{
    use HasFactory;

    protected $fillable = [
        'definition_id',
        'example_en',
        'example_fa',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(WordDefinition::class, 'definition_id');
    }
}
