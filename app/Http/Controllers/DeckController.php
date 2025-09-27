<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use Illuminate\Http\Request;

class DeckController extends Controller
{
    public function index()
    {
        $decks = auth()->user()->decks()
            ->withCount('cards')
            ->get();

        return view('decks.index', compact('decks'));
    }

    public function create()
    {
        return view('decks.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'new_cards_per_day' => 'integer|min:0|max:100',
            'review_cards_per_day' => 'integer|min:0|max:500'
        ]);

        $deck = auth()->user()->decks()->create($validated);

        return redirect()->route('decks.show', $deck)
            ->with('success', 'دسته با موفقیت ایجاد شد.');
    }

    public function show(Deck $deck)
    {
        $this->authorize('view', $deck);

        $stats = $deck->stats;
        $recentCards = $deck->cards()->latest()->take(5)->get();

        return view('decks.show', compact('deck', 'stats', 'recentCards'));
    }

    public function edit(Deck $deck)
    {
        $this->authorize('update', $deck);

        return view('decks.edit', compact('deck'));
    }

    public function update(Request $request, Deck $deck)
    {
        $this->authorize('update', $deck);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'new_cards_per_day' => 'integer|min:0|max:100',
            'review_cards_per_day' => 'integer|min:0|max:500',
            'is_active' => 'boolean'
        ]);

        $deck->update($validated);

        return redirect()->route('decks.show', $deck)
            ->with('success', 'دسته با موفقیت بروزرسانی شد.');
    }

    public function destroy(Deck $deck)
    {
        $this->authorize('delete', $deck);

        $deck->delete();

        return redirect()->route('decks.index')
            ->with('success', 'دسته با موفقیت حذف شد.');
    }
}