<?php

namespace App\Http\Controllers;

use App\Models\LogRead;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use App\Models\Deck;
use App\Models\Card;
use App\Models\CardReview;
use App\Http\Requests\StudyRequest;
use App\Enums\Rating;
use App\Services\ReviewCalculationService;
use Illuminate\Http\JsonResponse;

class StudyController extends Controller
{
    public function study(StudyRequest $request, string $path): JsonResponse
    {
        $deck = Deck::query()
            ->where('name', $path)
            ->where('user_id', auth()->id())
            ->first();

        if (!$deck) {
            return response()->json([
                'success' => false,
                'message' => 'هیچ کارتی برای مرور وجود ندارد.',
                'remainingCards' => 0
            ]);
        }

        $cardId = $request->card_id;
        $action = $request->action;

        if ($cardId && $action !== 'initial') {
            $card = Card::query()->find($cardId);
            $this->processReview($card, Rating::from($action));
        }

        $card = $this->getNextCard($deck);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'هیچ کارتی برای مرور وجود ندارد.',
                'remainingCards' => 0
            ]);
        }

        $userReview = $card->userReview;

        $reviewCalculationService = new ReviewCalculationService();
        $calculateNextInterval = $reviewCalculationService->calculateNextInterval($userReview);

        return response()->json([
            'success' => true,
            'card_id' => $card->id,
            'markdown' => $card->front,
            'front' => $card->front,
            'back' => $card->back,
            'remainingCards' => "Repetitions: {$userReview->repetitions}",
            'easy' => "Easy: {$calculateNextInterval->easyInterval}",
            'good' => "Good: {$calculateNextInterval->goodInterval}",
            'hard' => "Hard: {$calculateNextInterval->hardInterval}",
            'again' => "Again: {$calculateNextInterval->againInterval}",
        ]);
    }

    public function read(string $path, string $fileName): JsonResponse
    {
        $filePath = public_path("{$path}/{$fileName}.md");

        if (!File::exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        $fileContent = File::get($filePath);
        return response()->json([
            'success' => true,
            'markdown' => $fileContent,
            'name' => "{$path}/{$fileName}.md"
        ]);
    }

    public function track(Request $request): JsonResponse
    {
        LogRead::query()->create([
            'user_id' => auth()->id(),
            'is_main' => false,
            'name' => $request->name,
            'time' => 0,
            'day' => Carbon::now()->format('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getNextCard(Deck $deck): ?Card
    {
        $userId = auth()->id();

        $card = $this->getRelearningCard($deck, $userId);
        if ($card) return $card;

        $card = $this->getLearningCard($deck, $userId);
        if ($card) return $card;

        $card = $this->getReviewCard($deck, $userId);
        if ($card) return $card;

        return $this->getNewCard($deck, $userId);
    }

    private function getRelearningCard(Deck $deck, int $userId): ?Card
    {
        return $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'relearning')
                    ->where('due_date', '<=', now());
            })
            ->first();
    }

    private function getLearningCard(Deck $deck, int $userId): ?Card
    {
        return $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'learning')
                    ->where('due_date', '<=', now());
            })
            ->first();
    }

    private function getReviewCard(Deck $deck, int $userId): ?Card
    {
        $reviewCount = $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'review')
                    ->where('due_date', '<=', now())
                    ->whereDate('last_reviewed_at', '<', today());
            })
            ->count();

        if ($reviewCount > 0 && $this->getTodayReviewCount($deck) < $deck->review_cards_per_day) {
            return $deck->cards()
                ->whereHas('reviews', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->where('status', 'review')
                        ->where('due_date', '<=', now())
                        ->whereDate('last_reviewed_at', '<', today());
                })
                ->orderBy('created_at')
                ->first();
        }

        return null;
    }

    private function getNewCard(Deck $deck, int $userId): ?Card
    {
        if ($this->getTodayNewCount($deck) >= $deck->new_cards_per_day) {
            return null;
        }

        $card = $deck->cards()
            ->whereDoesntHave('reviews', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();

        if ($card) {
            $card->getOrCreateReview();
            return $card;
        }

        return $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'new');
            })
            ->first();
    }

    public function getDueCards(Request $request): \Illuminate\View\View
    {
        $deck = null;
        $path = $request->input('path');
        if ($path) {
            $deck = Deck::query()
                ->first();
        }

        $dueDate = $request->input('due_date') ? Carbon::parse($request->input('due_date')) : now();
        $userId = auth()->id();

        $relearningCards = $deck ? $this->getAllRelearningCards($deck, $userId, $dueDate) : collect();
        $learningCards = $deck ? $this->getAllLearningCards($deck, $userId, $dueDate) : collect();
        $reviewCards = $deck ? $this->getAllReviewCards($deck, $userId, $dueDate) : collect();
        $newCards = $deck ? $this->getAllNewCards($deck, $userId) : collect();

        return view('study.due-cards', [
            'deck' => $deck,
            'path' => $path,
            'time' => $dueDate->format('Y-m-d H:i:s'),
            'relearningCards' => $relearningCards,
            'learningCards' => $learningCards,
            'reviewCards' => $reviewCards,
            'newCards' => $newCards,
        ]);
    }

    private function getAllRelearningCards(Deck $deck, int $userId, $dueDate = null): \Illuminate\Support\Collection
    {
        $dueDate = $dueDate ?? now();
        return $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId, $dueDate) {
                $query->where('user_id', $userId)
                    ->where('status', 'relearning')
                    ->where('due_date', '<=', $dueDate);
            })
            ->get();
    }

    private function getAllLearningCards(Deck $deck, int $userId, $dueDate = null): \Illuminate\Support\Collection
    {
        $dueDate = $dueDate ?? now();
        return $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId, $dueDate) {
                $query->where('user_id', $userId)
                    ->where('status', 'learning')
                    ->where('due_date', '<=', $dueDate);
            })
            ->get();
    }

    private function getAllReviewCards(Deck $deck, int $userId, $dueDate = null): \Illuminate\Support\Collection
    {
        $dueDate = $dueDate ?? now();
        return $deck->cards()
            ->whereHas('reviews', function ($query) use ($userId, $dueDate) {
                $query->where('user_id', $userId)
                    ->where('status', 'review')
                    ->where('due_date', '<=', $dueDate)
                    ->whereDate('last_reviewed_at', '<', today());
            })
            ->orderBy('created_at')
            ->get();
    }

    private function getAllNewCards(Deck $deck, int $userId): \Illuminate\Support\Collection
    {
        $cards = collect();

        if ($this->getTodayNewCount($deck) < $deck->new_cards_per_day) {
            $newCards = $deck->cards()
                ->whereDoesntHave('reviews', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->get();

            foreach ($newCards as $card) {
                $card->getOrCreateReview();
                $cards->push($card);
            }

            $statusNewCards = $deck->cards()
                ->whereHas('reviews', function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->where('status', 'new');
                })
                ->get();

            $cards = $cards->merge($statusNewCards);
        }

        return $cards;
    }

    private function getDueCardsCount(Deck $deck): int
    {
        return $deck->cards()
            ->whereHas('reviews', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('due_date', '<=', now());
            })
            ->count();
    }


    private function getTodayNewCount(Deck $deck): int
    {
        return CardReview::query()->whereHas('card', function ($query) use ($deck) {
            $query->where('deck_id', $deck->id);
        })
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->where('status', '!=', 'new')
            ->count();
    }

    private function getTodayReviewCount(Deck $deck): int
    {
        return CardReview::query()->whereHas('card', function ($query) use ($deck) {
            $query->where('deck_id', $deck->id);
        })
            ->where('user_id', auth()->id())
            ->whereDate('last_reviewed_at', today())
            ->count();
    }

    private function getSessionStats(Deck $deck): array
    {
        $userId = auth()->id();
        $today = today();

        return [
            'studied_today' => CardReview::query()->whereHas('card', function ($query) use ($deck) {
                $query->where('deck_id', $deck->id);
            })
                ->where('user_id', $userId)
                ->whereDate('last_reviewed_at', $today)
                ->count(),
            'new_today' => $this->getTodayNewCount($deck),
            'remaining' => $this->getDueCardsCount($deck),
            'total_cards' => $deck->cards()->count()
        ];
    }

    private function processReview(Card $card, Rating $rating): void
    {
        $review = $card->getOrCreateReview();
        $review->processReview($rating);
    }
}
