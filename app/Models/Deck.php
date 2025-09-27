<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Deck represents a collection of flashcards for study
 * 
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property int $new_cards_per_day
 * @property int $review_cards_per_day
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Deck extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_active',
        'new_cards_per_day',
        'review_cards_per_day'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function getStatsAttribute()
    {
        $userId = auth()->id();
        
        return [
            'total_cards' => $this->cards()->count(),
            'new_cards' => $this->cards()
                ->whereHas('reviews', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('status', 'new');
                })->count(),
            'due_cards' => $this->cards()
                ->whereHas('reviews', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                          ->where('due_date', '<=', now());
                })->count(),
        ];
    }
}
