<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\FootballMatch;
use App\Models\MessageLog;
use App\Models\Pronostic;
use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Inscrits avec variation hebdomadaire
        $totalUsers = User::where('is_active', true)->count();
        $usersThisWeek = User::where('is_active', true)
            ->where('created_at', '>=', now()->subWeek())
            ->count();
        $usersLastWeek = User::where('is_active', true)
            ->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])
            ->count();

        $userGrowthPercent = $usersLastWeek > 0
            ? round((($usersThisWeek - $usersLastWeek) / $usersLastWeek) * 100, 1)
            : 0;

        // 2. Villages actifs
        $totalVillages = Village::where('is_active', true)->count();

        // Top 5 villages par nombre d'inscrits
        $topVillages = Village::withCount(['users' => function($query) {
            $query->where('is_active', true);
        }])
        ->having('users_count', '>', 0)
        ->orderByDesc('users_count')
        ->take(5)
        ->get();

        // 3. Pronostics cette semaine
        $pronosticsThisWeek = Pronostic::whereBetween('created_at', [now()->startOfWeek(), now()])
            ->count();

        $totalPronostics = Pronostic::count();

        // Taux de participation (utilisateurs avec au moins 1 pronostic)
        $usersWithPronostics = User::has('pronostics')->where('is_active', true)->count();
        $participationRate = $totalUsers > 0
            ? round(($usersWithPronostics / $totalUsers) * 100, 1)
            : 0;

        // 4. Messages envoyés (MessageLog + CampaignMessage)
        $messageLogTotal = MessageLog::count();
        $messageLogDelivered = MessageLog::where('status', 'delivered')->count();

        $campaignMessageTotal = CampaignMessage::whereIn('status', ['sent', 'delivered', 'failed'])->count();
        $campaignMessageDelivered = CampaignMessage::where('status', 'delivered')->count();

        $totalMessages = $messageLogTotal + $campaignMessageTotal;
        $messagesDelivered = $messageLogDelivered + $campaignMessageDelivered;

        $deliveryRate = $totalMessages > 0
            ? round(($messagesDelivered / $totalMessages) * 100, 1)
            : 0;

        // 5. Prochains matchs (5 prochains)
        $upcomingMatches = FootballMatch::where('status', 'scheduled')
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->take(5)
            ->get();

        // 6. Campagnes planifiées
        $plannedCampaigns = Campaign::whereIn('status', ['draft', 'scheduled'])
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        // 7. Évolution des inscriptions (7 derniers jours)
        $registrationChart = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 8. Statistiques par source (Twilio Studio tracking)
        $sourceStats = User::select('source_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('source_type')
            ->groupBy('source_type')
            ->orderByDesc('count')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowthPercent',
            'totalVillages',
            'topVillages',
            'pronosticsThisWeek',
            'totalPronostics',
            'participationRate',
            'totalMessages',
            'messagesDelivered',
            'deliveryRate',
            'upcomingMatches',
            'plannedCampaigns',
            'registrationChart',
            'sourceStats'
        ));
    }

    public function exportDetailedStats()
    {
        try {
            $filename = 'stats_detaillees_' . date('Y-m-d_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() {
                $file = fopen('php://output', 'w');

                try {

            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // ========== STATISTIQUES GÉNÉRALES ==========
            fputcsv($file, ['=== STATISTIQUES GÉNÉRALES ==='], ';');
            fputcsv($file, [''], ';');

            // Total des joueurs
            $totalUsers = User::where('is_active', true)->count();
            $totalUsersInactive = User::where('is_active', false)->count();
            fputcsv($file, ['Total Joueurs Actifs', $totalUsers], ';');
            fputcsv($file, ['Total Joueurs Inactifs', $totalUsersInactive], ';');
            fputcsv($file, ['Total Joueurs (Tous)', $totalUsers + $totalUsersInactive], ';');
            fputcsv($file, [''], ';');

            // Total des villages
            $totalVillages = Village::where('is_active', true)->count();
            fputcsv($file, ['Total Villages Actifs', $totalVillages], ';');
            fputcsv($file, [''], ';');

            // Total des matchs
            $totalMatches = FootballMatch::count();
            $matchesScheduled = FootballMatch::where('status', 'scheduled')->count();
            $matchesCompleted = FootballMatch::where('status', 'completed')->count();
            $matchesCancelled = FootballMatch::where('status', 'cancelled')->count();
            fputcsv($file, ['Total Matchs', $totalMatches], ';');
            fputcsv($file, ['Matchs Programmés', $matchesScheduled], ';');
            fputcsv($file, ['Matchs Terminés', $matchesCompleted], ';');
            fputcsv($file, ['Matchs Annulés', $matchesCancelled], ';');
            fputcsv($file, [''], ';');

            // Total des pronostics
            $totalPronostics = Pronostic::count();
            fputcsv($file, ['Total Pronostics', $totalPronostics], ';');
            fputcsv($file, [''], ';');

            // Messages
            $messageLogTotal = MessageLog::count();
            $messageLogDelivered = MessageLog::where('status', 'delivered')->count();
            $campaignMessageTotal = CampaignMessage::whereIn('status', ['sent', 'delivered', 'failed'])->count();
            $campaignMessageDelivered = CampaignMessage::where('status', 'delivered')->count();
            $totalMessages = $messageLogTotal + $campaignMessageTotal;
            $messagesDelivered = $messageLogDelivered + $campaignMessageDelivered;
            $deliveryRate = $totalMessages > 0 ? round(($messagesDelivered / $totalMessages) * 100, 2) : 0;

            fputcsv($file, ['Total Messages Envoyés', $totalMessages], ';');
            fputcsv($file, ['Messages Délivrés', $messagesDelivered], ';');
            fputcsv($file, ['Taux de Livraison (%)', $deliveryRate], ';');
            fputcsv($file, [''], ';');
            fputcsv($file, [''], ';');

            // ========== INSCRIPTIONS PAR PÉRIODE ==========
            fputcsv($file, ['=== INSCRIPTIONS PAR PÉRIODE ==='], ';');
            fputcsv($file, [''], ';');

            // Par jour (30 derniers jours)
            fputcsv($file, ['Date', 'Nombre d\'Inscrits'], ';');
            $dailyRegistrations = User::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            foreach ($dailyRegistrations as $day) {
                fputcsv($file, [$day->date, $day->count], ';');
            }
            fputcsv($file, [''], ';');

            // Par semaine (12 dernières semaines)
            fputcsv($file, ['Semaine', 'Nombre d\'Inscrits'], ';');
            $weeklyRegistrations = User::select(
                    DB::raw('YEARWEEK(created_at) as week'),
                    DB::raw('MIN(DATE(created_at)) as start_date'),
                    DB::raw('MAX(DATE(created_at)) as end_date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subWeeks(12))
                ->groupBy('week')
                ->orderBy('week')
                ->get();

            foreach ($weeklyRegistrations as $week) {
                $label = "Semaine {$week->start_date} au {$week->end_date}";
                fputcsv($file, [$label, $week->count], ';');
            }
            fputcsv($file, [''], ';');

            // Par mois (12 derniers mois)
            fputcsv($file, ['Mois', 'Nombre d\'Inscrits'], ';');
            $monthlyRegistrations = User::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            foreach ($monthlyRegistrations as $month) {
                fputcsv($file, [$month->month, $month->count], ';');
            }
            fputcsv($file, [''], ';');
            fputcsv($file, [''], ';');

            // ========== STATISTIQUES PAR VILLAGE ==========
            fputcsv($file, ['=== STATISTIQUES PAR VILLAGE ==='], ';');
            fputcsv($file, [''], ';');
            fputcsv($file, ['Village', 'Nombre d\'Inscrits', 'Nombre de Pronostics'], ';');

            $villageStats = Village::withCount(['users' => function($query) {
                    $query->where('is_active', true);
                }])
                ->orderByDesc('users_count')
                ->get();

            foreach ($villageStats as $village) {
                // Compter les pronostics via les utilisateurs du village
                $pronosticsCount = Pronostic::whereHas('user', function($query) use ($village) {
                    $query->where('village_id', $village->id);
                })->count();

                fputcsv($file, [
                    $village->name,
                    $village->users_count,
                    $pronosticsCount
                ], ';');
            }
            fputcsv($file, [''], ';');
            fputcsv($file, [''], ';');

            // ========== STATISTIQUES PAR MATCH ==========
            fputcsv($file, ['=== STATISTIQUES PAR MATCH (Gagnants/Perdants) ==='], ';');
            fputcsv($file, [''], ';');
            fputcsv($file, [
                'Match',
                'Date',
                'Statut',
                'Résultat',
                'Total Pronostics',
                'Gagnants',
                'Perdants'
            ], ';');

            $matches = FootballMatch::with('pronostics')
                ->orderBy('match_date', 'desc')
                ->get();

            foreach ($matches as $match) {
                $totalPronostics = $match->pronostics->count();
                $winners = $match->pronostics->where('is_winner', true)->count();
                $losers = $match->pronostics->where('is_winner', false)->count();

                $result = $match->final_score_a !== null && $match->final_score_b !== null
                    ? "{$match->final_score_a} - {$match->final_score_b}"
                    : 'N/A';

                fputcsv($file, [
                    "{$match->team_a} vs {$match->team_b}",
                    $match->match_date->format('Y-m-d H:i'),
                    $match->status,
                    $result,
                    $totalPronostics,
                    $winners,
                    $losers
                ], ';');
            }
            fputcsv($file, [''], ';');
            fputcsv($file, [''], ';');

            // ========== PRONOSTICS PAR TYPE ==========
            fputcsv($file, ['=== PRONOSTICS PAR TYPE DE PRÉDICTION ==='], ';');
            fputcsv($file, [''], ';');
            fputcsv($file, ['Type de Pronostic', 'Nombre'], ';');

            $pronosticsByType = Pronostic::select('prediction_type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('prediction_type')
                ->groupBy('prediction_type')
                ->orderByDesc('count')
                ->get();

            foreach ($pronosticsByType as $type) {
                fputcsv($file, [$type->prediction_type, $type->count], ';');
            }
            fputcsv($file, [''], ';');
            fputcsv($file, [''], ';');

            // ========== INSCRIPTIONS PAR SOURCE ==========
            fputcsv($file, ['=== INSCRIPTIONS PAR SOURCE ==='], ';');
            fputcsv($file, [''], ';');
            fputcsv($file, ['Source', 'Nombre d\'Inscrits'], ';');

            $sourceStats = User::select('source_type', DB::raw('COUNT(*) as count'))
                ->whereNotNull('source_type')
                ->groupBy('source_type')
                ->orderByDesc('count')
                ->get();

            foreach ($sourceStats as $source) {
                fputcsv($file, [$source->source_type ?: 'Autre', $source->count], ';');
            }

            // Source NULL
            $nullSource = User::whereNull('source_type')->count();
            if ($nullSource > 0) {
                fputcsv($file, ['Non spécifié', $nullSource], ';');
            }

            fputcsv($file, [''], ';');
            fputcsv($file, [''], ';');

            // ========== FOOTER ==========
            fputcsv($file, [''], ';');
            fputcsv($file, ['Rapport généré le', date('Y-m-d H:i:s')], ';');

                } catch (\Exception $e) {
                    // En cas d'erreur, on écrit l'erreur dans le CSV au lieu de générer du HTML
                    fputcsv($file, [''], ';');
                    fputcsv($file, ['=== ERREUR ==='], ';');
                    fputcsv($file, ['Une erreur est survenue lors de la génération du rapport'], ';');
                    fputcsv($file, ['Message d\'erreur : ' . $e->getMessage()], ';');
                    fputcsv($file, ['Fichier : ' . $e->getFile() . ' (ligne ' . $e->getLine() . ')'], ';');
                } finally {
                    fclose($file);
                }
            };

            return Response::stream($callback, 200, $headers);

        } catch (\Exception $e) {
            // Si l'erreur survient avant le streaming, on affiche une page d'erreur normale
            return back()->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }
}
