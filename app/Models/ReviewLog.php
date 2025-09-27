<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ReviewLog represents a historical record of card reviews
 * 
 * @property int $id
 * @property int $card_id
 * @property int $user_id
 * @property string $rating
 * @property int $time_taken
 * @property float $ease_factor_before
 * @property float $ease_factor_after
 * @property int $interval_before
 * @property int $interval_after
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ReviewLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'user_id',
        'rating',
        'time_taken',
        'ease_factor_before',
        'ease_factor_after',
        'interval_before',
        'interval_after'
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
