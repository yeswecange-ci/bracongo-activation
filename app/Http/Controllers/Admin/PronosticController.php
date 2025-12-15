<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\Pronostic;
use App\Models\User;
use Illuminate\Http\Request;

class PronosticController extends Controller
{
    /**
     * Afficher tous les pronostics
     */
    public function index(Request $request)
    {
        $query = Pronostic::with(['user', 'match'])
            ->orderBy('created_at', 'desc');

        // Filtre par match
        if ($request->filled('match_id')) {
            $query->where('match_id', $request->match_id);
        }

        // Filtre par statut (gagnant/perdant)
        if ($request->filled('is_winner')) {
            $query->where('is_winner', $request->is_winner === '1');
        }

        // Filtre par utilisateur
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $pronostics = $query->paginate(20);

        // Pour les filtres
        $matches = FootballMatch::orderBy('match_date', 'desc')->get();

        return view('admin.pronostics.index', compact('pronostics', 'matches'));
    }

    /**
     * Afficher les détails d'un pronostic
     */
    public function show(Pronostic $pronostic)
    {
        $pronostic->load(['user.village', 'match']);
        return view('admin.pronostics.show', compact('pronostic'));
    }

    /**
     * Afficher les pronostics pour un match spécifique
     */
    public function byMatch(FootballMatch $match)
    {
        $pronostics = $match->pronostics()
            ->with(['user.village'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Statistiques
        $stats = [
            'total' => $pronostics->count(),
            'winners' => $pronostics->where('is_winner', true)->count(),
            'by_prediction' => $pronostics->groupBy(function ($p) {
                // Utiliser prediction_text qui gère les deux modes
                return $p->prediction_text;
            })->map->count()->sortDesc(),
        ];

        return view('admin.pronostics.by-match', compact('match', 'pronostics', 'stats'));
    }

    /**
     * Supprimer un pronostic (admin seulement)
     */
    public function destroy(Pronostic $pronostic)
    {
        $pronostic->delete();

        return redirect()->back()
            ->with('success', 'Pronostic supprimé avec succès.');
    }

    /**
     * Statistiques globales des pronostics
     */
    public function stats()
    {
        $stats = [
            'total_pronostics' => Pronostic::count(),
            'total_winners' => Pronostic::where('is_winner', true)->count(),
            'by_match' => Pronostic::selectRaw('match_id, count(*) as total')
                ->groupBy('match_id')
                ->with('match')
                ->get(),
            'top_users' => User::withCount(['pronostics' => function ($q) {
                $q->where('is_winner', true);
            }])
                ->having('pronostics_count', '>', 0)
                ->orderBy('pronostics_count', 'desc')
                ->take(10)
                ->get(),
        ];

        return view('admin.pronostics.stats', compact('stats'));
    }
}
