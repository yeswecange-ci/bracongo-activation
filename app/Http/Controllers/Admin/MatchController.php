<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function index()
    {
        $matches = FootballMatch::withCount('pronostics')->orderBy('match_date', 'desc')->paginate(10);
        return view('admin.matches.index', compact('matches'));
    }

    public function create()
    {
        return view('admin.matches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_a' => 'required|string|max:255',
            'team_b' => 'required|string|max:255',
            'match_date' => 'required|date',
            'status' => 'required|in:scheduled,live,finished',
        ]);

        $validated['pronostic_enabled'] = $request->has('pronostic_enabled');

        FootballMatch::create($validated);

        return redirect()->route('admin.matches.index')
            ->with('success', 'Match créé avec succès !');
    }

    public function show(FootballMatch $match)
    {
        $match->load(['pronostics.user', 'prizeWinners.user', 'prizeWinners.prize']);
        return view('admin.matches.show', compact('match'));
    }

    public function edit(FootballMatch $match)
    {
        return view('admin.matches.edit', compact('match'));
    }

    public function update(Request $request, FootballMatch $match)
    {
        $validated = $request->validate([
            'team_a' => 'required|string|max:255',
            'team_b' => 'required|string|max:255',
            'match_date' => 'required|date',
            'score_a' => 'nullable|integer|min:0',
            'score_b' => 'nullable|integer|min:0',
            'status' => 'required|in:scheduled,live,finished',
        ]);

        $validated['pronostic_enabled'] = $request->has('pronostic_enabled');

        $match->update($validated);

        return redirect()->route('admin.matches.index')
            ->with('success', 'Match mis à jour avec succès !');
    }

    public function destroy(FootballMatch $match)
    {
        $match->delete();

        return redirect()->route('admin.matches.index')
            ->with('success', 'Match supprimé avec succès !');
    }
}
