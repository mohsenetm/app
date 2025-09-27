<?php

namespace App\Services;

use App\Enums\Rating;
use App\Models\CardReview;
use App\DTOs\IntervalDTO;

class ReviewCalculationService
{
    private const MIN_EASE_FACTOR = 1.3;
    private const EASE_FACTOR_DECREMENT = 0.15;
    private const EASE_FACTOR_INCREMENT = 0.15;
    private const HARD_MULTIPLIER = 0.6;
    private const GOOD_MULTIPLIER = 1.0;
    private const EASY_MULTIPLIER = 1.3;
    private const FIRST_REVIEW_HARD_INTERVAL = 3;
    private const FIRST_REVIEW_GOOD_EASY_INTERVAL = 4;

    public function calculateNextInterval(CardReview $cardReview): IntervalDTO
    {
        $repetitions = $cardReview->repetitions;
        $interval = $cardReview->interval;
        $easeFactor = $cardReview->ease_factor;

        $intervalDto = new IntervalDTO();

        $intervalDto->againInterval = $this->calculateIntervalForRating(
            Rating::AGAIN, $repetitions, $interval, $easeFactor
        );

        $intervalDto->hardInterval = $this->calculateIntervalForRating(
            Rating::HARD, $repetitions, $interval, $easeFactor
        );

        $intervalDto->goodInterval = $this->calculateIntervalForRating(
            Rating::GOOD, $repetitions, $interval, $easeFactor
        );

        $intervalDto->easyInterval = $this->calculateIntervalForRating(
            Rating::EASY, $repetitions, $interval, $easeFactor
        );

        return $intervalDto;
    }

    private function calculateIntervalForRating(Rating $rating, int $repetitions, int $interval, float $easeFactor): int
    {
        if ($rating === Rating::AGAIN) {
            return 1;
        }

        $adjustedEaseFactor = $this->adjustEaseFactor($rating, $easeFactor);

        if ($repetitions === 0) {
            return 1;
        }

        if ($repetitions === 1) {
            return $rating === Rating::HARD
                ? self::FIRST_REVIEW_HARD_INTERVAL
                : self::FIRST_REVIEW_GOOD_EASY_INTERVAL;
        }

        $multiplier = $this->getMultiplierForRating($rating);
        return round($interval * $adjustedEaseFactor * $multiplier);
    }

    private function adjustEaseFactor(Rating $rating, float $easeFactor): float
    {
        return match ($rating) {
            Rating::HARD => max(self::MIN_EASE_FACTOR, $easeFactor - self::EASE_FACTOR_DECREMENT),
            Rating::GOOD => $easeFactor,
            Rating::EASY => $easeFactor + self::EASE_FACTOR_INCREMENT,
            Rating::AGAIN => INF,
        };
    }

    private function getMultiplierForRating(Rating $rating): float
    {
        return match ($rating) {
            Rating::HARD => self::HARD_MULTIPLIER,
            Rating::GOOD => self::GOOD_MULTIPLIER,
            Rating::EASY => self::EASY_MULTIPLIER,
            Rating::AGAIN => INF,
        };
    }
}
