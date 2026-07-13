<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\Pronostic;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Construit l'ensemble des statistiques d'activation (CAN + Coupe du Monde)
 * partagées par l'export PDF et l'export Excel.
 *
 * La base ne stocke pas la compétition : on la déduit de la date du match.
 * La Coupe du Monde 2026 a débuté le 10 juin 2026.
 */
class StatsReportService
{
    /** Date de bascule CAN -> Coupe du Monde. */
    public const WORLD_CUP_START = '2026-06-10 00:00:00';

    public const COMP_CAN = 'CAN 2025';
    public const COMP_CDM = 'Coupe du Monde 2026';

    /** Label de la compétition d'un match selon sa date. */
    public function competitionFor(FootballMatch $match): string
    {
        return $match->match_date->gte(Carbon::parse(self::WORLD_CUP_START))
            ? self::COMP_CDM
            : self::COMP_CAN;
    }

    /**
     * Toutes les statistiques, prêtes à être rendues.
     *
     * @return array<string,mixed>
     */
    public function build(): array
    {
        $matches = FootballMatch::with(['pronostics'])
            ->orderBy('match_date')
            ->get();

        $matchRows = $this->matchRows($matches);

        return [
            'generated_at'   => now(),
            'overview'       => $this->overview($matchRows),
            'by_competition' => $this->byCompetition($matchRows),
            'matches'        => $matchRows,
            'registrations'  => $this->registrations(),
            'localities'     => $this->localities(),
            'top_players'    => $this->topPlayers(),
        ];
    }

    /**
     * Détail par match, avec compétition, gagnants/perdants et taux.
     *
     * @return array<int,array<string,mixed>>
     */
    private function matchRows($matches): array
    {
        return $matches->map(function (FootballMatch $m) {
            $total    = $m->pronostics->count();
            $winners  = $m->pronostics->where('is_winner', true)->count();
            $exact    = $m->pronostics->where('points_won', Pronostic::POINTS_EXACT_SCORE)->count();
            $losers   = $total - $winners;
            $finished = $m->status === 'finished';

            return [
                'id'            => $m->id,
                'competition'   => $this->competitionFor($m),
                'date'          => $m->match_date,
                'team_a'        => $m->team_a,
                'team_b'        => $m->team_b,
                'label'         => $m->team_a . ' vs ' . $m->team_b,
                'status'        => $m->status,
                'score'         => $finished && $m->score_a !== null
                    ? $m->score_a . ' - ' . $m->score_b
                    : '—',
                'total'         => $total,
                'winners'       => $winners,
                'losers'        => $losers,
                'exact'         => $exact,
                'points'        => (int) $m->pronostics->sum('points_won'),
                'success_rate'  => $total > 0 ? round($winners / $total * 100, 1) : 0.0,
            ];
        })->all();
    }

    /**
     * Chiffres globaux toutes activations confondues.
     *
     * @param array<int,array<string,mixed>> $matchRows
     * @return array<string,mixed>
     */
    private function overview(array $matchRows): array
    {
        $activeUsers   = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $totalUsers    = $activeUsers + $inactiveUsers;

        $usersWithPronostics = User::has('pronostics')->count();

        $totalPronostics = array_sum(array_column($matchRows, 'total'));
        $totalWinners    = array_sum(array_column($matchRows, 'winners'));
        $totalLosers     = array_sum(array_column($matchRows, 'losers'));
        $totalExact      = array_sum(array_column($matchRows, 'exact'));
        $totalPoints     = array_sum(array_column($matchRows, 'points'));

        return [
            'total_users'          => $totalUsers,
            'active_users'         => $activeUsers,
            'inactive_users'       => $inactiveUsers,
            'users_with_pronostics'=> $usersWithPronostics,
            'participation_rate'   => $totalUsers > 0
                ? round($usersWithPronostics / $totalUsers * 100, 1)
                : 0.0,
            'total_matches'        => count($matchRows),
            'total_pronostics'     => $totalPronostics,
            'total_winners'        => $totalWinners,
            'total_losers'         => $totalLosers,
            'total_exact'          => $totalExact,
            'total_points'         => $totalPoints,
            'global_success_rate'  => $totalPronostics > 0
                ? round($totalWinners / $totalPronostics * 100, 1)
                : 0.0,
        ];
    }

    /**
     * Agrégats par compétition (CAN vs Coupe du Monde).
     *
     * @param array<int,array<string,mixed>> $matchRows
     * @return array<int,array<string,mixed>>
     */
    private function byCompetition(array $matchRows): array
    {
        $order = [self::COMP_CAN, self::COMP_CDM];
        $out   = [];

        foreach ($order as $comp) {
            $rows = array_values(array_filter(
                $matchRows,
                fn ($r) => $r['competition'] === $comp
            ));

            if (empty($rows)) {
                continue;
            }

            $total   = array_sum(array_column($rows, 'total'));
            $winners = array_sum(array_column($rows, 'winners'));

            $out[] = [
                'competition'  => $comp,
                'matches'      => count($rows),
                'pronostics'   => $total,
                'winners'      => $winners,
                'losers'       => array_sum(array_column($rows, 'losers')),
                'exact'        => array_sum(array_column($rows, 'exact')),
                'points'       => array_sum(array_column($rows, 'points')),
                'success_rate' => $total > 0 ? round($winners / $total * 100, 1) : 0.0,
            ];
        }

        return $out;
    }

    /**
     * Stats sur les inscrits : statut, source, évolution mensuelle.
     *
     * @return array<string,mixed>
     */
    private function registrations(): array
    {
        $byStatus = User::select('registration_status', DB::raw('COUNT(*) as count'))
            ->groupBy('registration_status')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->registration_status ?: 'Non spécifié',
                'count' => (int) $r->count,
            ])
            ->all();

        $bySource = User::select('source_type', DB::raw('COUNT(*) as count'))
            ->groupBy('source_type')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->source_type ?: 'Non spécifié',
                'count' => (int) $r->count,
            ])
            ->all();

        $byMonth = User::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'label' => $r->month,
                'count' => (int) $r->count,
            ])
            ->all();

        return [
            'by_status' => $byStatus,
            'by_source' => $bySource,
            'by_month'  => $byMonth,
        ];
    }

    /**
     * Répartition géographique des inscrits, à partir du champ adresse libre.
     * On normalise le texte pour regrouper les variantes d'une même commune.
     *
     * @return array<int,array<string,mixed>>
     */
    private function localities(): array
    {
        $addresses = User::whereNotNull('address')
            ->where('address', '!=', '')
            ->pluck('address');

        $counts = [];
        foreach ($addresses as $address) {
            $commune = $this->normalizeCommune($address);
            $counts[$commune] = ($counts[$commune] ?? 0) + 1;
        }

        $notProvided = User::where(function ($q) {
            $q->whereNull('address')->orWhere('address', '');
        })->count();

        arsort($counts);

        $rows = [];
        foreach ($counts as $commune => $count) {
            $rows[] = ['commune' => $commune, 'count' => $count];
        }

        return [
            'rows'         => $rows,
            'not_provided' => $notProvided,
            'total_geo'    => $addresses->count(),
        ];
    }

    /**
     * Normalise une adresse libre en un nom de commune canonique.
     * Ex : "Kinshasa, Ngaliema", "Kinshasa ngaliema" -> "Ngaliema".
     */
    private function normalizeCommune(string $raw): string
    {
        $s = trim($raw);

        // Translittération accents -> ASCII, minuscules.
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        if ($ascii !== false) {
            $s = $ascii;
        }
        $s = mb_strtolower($s);

        // Séparateurs -> espace, ponctuation résiduelle supprimée.
        $s = preg_replace('/[,;\/\-\.]+/', ' ', $s);
        $s = preg_replace("/[^a-z0-9'\\s]/", ' ', $s);
        $s = preg_replace('/\s+/', ' ', trim($s));

        if ($s === '') {
            return 'Non précisé';
        }

        // Retirer un préfixe "kinshasa" quand une commune suit.
        if (str_starts_with($s, 'kinshasa') && $s !== 'kinshasa') {
            $s = trim(substr($s, strlen('kinshasa')));
        }

        if ($s === '' || $s === 'kinshasa') {
            return 'Kinshasa (commune non précisée)';
        }

        // Title case (gère les noms composés type "mont ngafula").
        return ucwords($s);
    }

    /**
     * Classement des meilleurs joueurs, tous matchs confondus.
     *
     * @return array<int,array<string,mixed>>
     */
    private function topPlayers(int $limit = 20): array
    {
        return User::select('users.id', 'users.name', 'users.phone', 'users.address')
            ->selectRaw('COALESCE(SUM(pronostics.points_won),0) as total_points')
            ->selectRaw('COUNT(pronostics.id) as total_pronostics')
            ->selectRaw('SUM(CASE WHEN pronostics.is_winner = 1 THEN 1 ELSE 0 END) as total_wins')
            ->selectRaw('SUM(CASE WHEN pronostics.points_won = ? THEN 1 ELSE 0 END) as exact_scores', [Pronostic::POINTS_EXACT_SCORE])
            ->join('pronostics', 'users.id', '=', 'pronostics.user_id')
            ->groupBy('users.id', 'users.name', 'users.phone', 'users.address')
            ->having('total_points', '>', 0)
            ->orderByDesc('total_points')
            ->orderByDesc('total_wins')
            ->take($limit)
            ->get()
            ->map(fn ($u, $i) => [
                'rank'        => $i + 1,
                'name'        => $u->name,
                'phone'       => $u->phone,
                'address'     => $u->address,
                'points'      => (int) $u->total_points,
                'pronostics'  => (int) $u->total_pronostics,
                'wins'        => (int) $u->total_wins,
                'exact'       => (int) $u->exact_scores,
            ])
            ->all();
    }
}
