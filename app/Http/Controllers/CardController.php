<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Deck;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function create(Deck $deck)
    {
        return view('cards.create', compact('deck'));
    }

    public function store(Request $request, Deck $deck)
    {
        $validated = $request->validate([
            'front' => 'required|string',
            'back' => 'required|string',
            'notes' => 'nullable|string',
            'type' => 'in:basic,reverse,cloze',
            'tags' => 'nullable|array'
        ]);

        $card = $deck->cards()->create($validated);

        // ایجاد رکورد review برای کاربر
        $card->getOrCreateReview();

        return redirect()->route('decks.show', $deck)
            ->with('success', 'کارت با موفقیت اضافه شد.');
    }

    public function edit(Card $card)
    {
        $this->authorize('update', $card->deck);

        return view('cards.edit', compact('card'));
    }

    public function update(Request $request, Card $card)
    {
        $this->authorize('update', $card->deck);

        $validated = $request->validate([
            'front' => 'required|string',
            'back' => 'required|string',
            'notes' => 'nullable|string',
            'type' => 'in:basic,reverse,cloze',
            'tags' => 'nullable|array'
        ]);

        $card->update($validated);

        return redirect()->route('decks.show', $card->deck)
            ->with('success', 'کارت با موفقیت بروزرسانی شد.');
    }

    public function destroy(Card $card)
    {
        $this->authorize('delete', $card->deck);

        $deck = $card->deck;
        $card->delete();

        return redirect()->route('decks.show', $deck)
            ->with('success', 'کارت با موفقیت حذف شد.');
    }

    public function import(Request $request, Deck $deck)
    {
        $this->authorize('update', $deck);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file);
        $lines = explode("\n", $content);

        $imported = 0;
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            $parts = str_getcsv($line, "\t"); // Tab-separated
            if (count($parts) >= 2) {
                $card = $deck->cards()->create([
                    'front' => $parts[0],
                    'back' => $parts[1],
                    'notes' => $parts[2] ?? null,
                    'type' => 'basic'
                ]);

                $card->getOrCreateReview();
                $imported++;
            }
        }

        return back()->with('success', "$imported کارت با موفقیت وارد شد.");
    }
}
