<?php

namespace App\Models;

use App\Enums\Rating;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * CardReview represents a user's review progress for a specific flashcard
 *
 * @property int $id
 * @property int $card_id
 * @property int $user_id
 * @property ReviewStatus $status
 * @property float $ease_factor
 * @property int $interval
 * @property int $repetitions
 * @property \Illuminate\Support\Carbon $due_date
 * @property \Illuminate\Support\Carbon $last_reviewed_at
 * @property int $review_count
 * @property int $lapses
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class CardReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_id',
        'user_id',
        'status',
        'ease_factor',
        'interval',
        'repetitions',
        'due_date',
        'last_reviewed_at',
        'review_count',
        'lapses'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'last_reviewed_at' => 'datetime',
        'ease_factor' => 'float',
        'status' => ReviewStatus::class,
    ];

    private const MIN_EASE_FACTOR = 1.3;
    private const EASE_FACTOR_DECREMENT_AGAIN = 0.2;
    private const EASE_FACTOR_DECREMENT_HARD = 0.15;
    private const EASE_FACTOR_INCREMENT_EASY = 0.15;
    private const AGAIN_INTERVAL = 1;
    private const FIRST_REVIEW_INTERVAL = 1;
    private const FIRST_REVIEW_HARD_INTERVAL = 3;
    private const FIRST_REVIEW_GOOD_EASY_INTERVAL = 4;
    private const HARD_MULTIPLIER = 0.6;
    private const GOOD_MULTIPLIER = 1.0;
    private const EASY_MULTIPLIER = 1.3;

    public function card()
    {
        return $this->belongsTo(Card::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isDue()
    {
        return $this->due_date <= now();
    }

    /**
     * Get the current ease factor
     */
    public function getEaseFactor(): float
    {
        return $this->ease_factor;
    }

    /**
     * Get the current repetitions count
     */
    public function getRepetitions(): int
    {
        return $this->repetitions;
    }

    /**
     * Get the current interval in days
     */
    public function getInterval(): int
    {
        return $this->interval;
    }

    /**
     * Process a card review and update the review status based on performance
     * 
     * Status Transitions:
     * - NEW: Initial state for cards that haven't been reviewed
     * - LEARNING: Card transitions from NEW after first successful review (not AGAIN)
     * - REVIEW: Card graduates to REVIEW after 2+ successful reviews (not AGAIN)
     * - RELEARNING: Card resets to RELEARNING when rated AGAIN
     * 
     * The status calculation considers:
     * 1. Current status (NEW cards start in learning phase)
     * 2. Number of successful repetitions (2+ repetitions graduate to REVIEW)
     * 3. Current rating (AGAIN resets to RELEARNING, others progress forward)
     */
    public function processReview(Rating $rating): void
    {
        $oldEaseFactor = $this->ease_factor;
        $oldInterval = $this->interval;

        ReviewLog::query()->create([
            'card_id' => $this->card_id,
            'user_id' => $this->user_id,
            'rating' => $rating->value,
            'ease_factor_before' => $oldEaseFactor,
            'ease_factor_after' => $this->ease_factor,
            'interval_before' => $oldInterval,
            'interval_after' => $this->interval,
        ]);

        match ($rating) {
            Rating::AGAIN => $this->processAgainRating(),
            Rating::HARD => $this->processHardRating(),
            Rating::GOOD => $this->processGoodRating(),
            Rating::EASY => $this->processEasyRating(),
        };

        if ($this->status === ReviewStatus::NEW && $rating !== Rating::AGAIN) {
            $this->status = ReviewStatus::LEARNING;
        } elseif ($this->repetitions >= 2 && $rating !== Rating::AGAIN) {
            $this->status = ReviewStatus::REVIEW;
        }

        $this->due_date = Carbon::now()->addDays($this->interval);
        $this->last_reviewed_at = now();
        $this->review_count++;

        $this->save();
    }

    private function processAgainRating(): void
    {
        $this->repetitions = 0;
        $this->lapses++;
        $this->status = ReviewStatus::RELEARNING;
        $this->ease_factor = max(self::MIN_EASE_FACTOR, $this->ease_factor - self::EASE_FACTOR_DECREMENT_AGAIN);
        $this->calculateInterval(Rating::AGAIN);
    }

    private function processHardRating(): void
    {
        $this->ease_factor = max(self::MIN_EASE_FACTOR, $this->ease_factor - self::EASE_FACTOR_DECREMENT_HARD);
        $this->calculateInterval(Rating::HARD);
        $this->repetitions++;
    }

    private function processGoodRating(): void
    {
        $this->calculateInterval(Rating::GOOD);
        $this->repetitions++;
    }

    private function processEasyRating(): void
    {
        $this->ease_factor = $this->ease_factor + self::EASE_FACTOR_INCREMENT_EASY;
        $this->calculateInterval(Rating::EASY);
        $this->repetitions++;
    }

    private function calculateInterval(Rating $rating): void
    {
        if ($rating === Rating::AGAIN) {
            $this->interval = self::AGAIN_INTERVAL;
            return;
        }

        if ($this->repetitions === 0) {
            $this->interval = self::FIRST_REVIEW_INTERVAL;
            return;
        }

        if ($this->repetitions === 1) {
            $this->interval = match ($rating) {
                Rating::HARD => self::FIRST_REVIEW_HARD_INTERVAL,
                Rating::GOOD, Rating::EASY => self::FIRST_REVIEW_GOOD_EASY_INTERVAL,
            };
            return;
        }

        $multiplier = match ($rating) {
            Rating::HARD => self::HARD_MULTIPLIER,
            Rating::GOOD => self::GOOD_MULTIPLIER,
            Rating::EASY => self::EASY_MULTIPLIER,
            Rating::AGAIN => 0, // This case is already handled above
        };

        $this->interval = round($this->interval * $this->ease_factor * $multiplier);
    }
}
