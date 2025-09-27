<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Card represents a flashcard in a deck
 *
 * @property int $id
 * @property int $deck_id
 * @property string $front
 * @property string $back
 * @property string|null $notes
 * @property string $type
 * @property array $tags
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property CardReview $userReview
 */
class Card extends Model
{
    use HasFactory;
    protected $fillable = [
        'deck_id',
        'front',
        'back',
        'notes',
        'type',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function deck()
    {
        return $this->belongsTo(Deck::class);
    }

    public function reviews()
    {
        return $this->hasMany(CardReview::class);
    }

    public function reviewLogs()
    {
        return $this->hasMany(ReviewLog::class);
    }

    public function userReview()
    {
        return $this->hasOne(CardReview::class)
            ->where('user_id', auth()->id());
    }

    public function getOrCreateReview($userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $this->reviews()->firstOrCreate(
            ['user_id' => $userId],
            [
                'status' => 'new',
                'ease_factor' => 2.5,
                'interval' => 0,
                'repetitions' => 0,
                'due_date' => now()
            ]
        );
    }
}
