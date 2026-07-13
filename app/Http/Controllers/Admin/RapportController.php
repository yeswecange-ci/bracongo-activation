<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatsReportService;
use App\Support\SimpleXlsxWriter;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Rapport global des activations (CAN + Coupe du Monde) :
 * page de synthèse + exports PDF stylisé et Excel multi-feuilles.
 */
class RapportController extends Controller
{
    public function __construct(private StatsReportService $stats)
    {
    }

    /** Page d'aperçu avec les chiffres clés et les boutons d'export. */
    public function index()
    {
        $data = $this->stats->build();

        return view('admin.rapport.index', ['data' => $data]);
    }

    /** Export PDF stylisé. */
    public function exportPdf()
    {
        $data = $this->stats->build();

        $pdf = Pdf::loadView('admin.rapport.pdf', ['data' => $data])
            ->setPaper('a4', 'portrait');

        $filename = 'rapport-activations-bracongo_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /** Export Excel (.xlsx) multi-feuilles. */
    public function exportExcel()
    {
        $data = $this->stats->build();
        $x    = new SimpleXlsxWriter();

        $this->sheetOverview($x, $data);
        $this->sheetMatches($x, $data);
        $this->sheetPlayers($x, $data);
        $this->sheetLocalities($x, $data);
        $this->sheetRegistrations($x, $data);

        $filename = 'rapport-activations-bracongo_' . now()->format('Y-m-d') . '.xlsx';

        return $x->download($filename);
    }

    // ── Feuilles Excel ────────────────────────────────────────────

    private function sheetOverview(SimpleXlsxWriter $x, array $data): void
    {
        $o = $data['overview'];

        $x->addSheet("Vue d'ensemble", function ($s) use ($o, $data) {
            $s->title('Rapport global des activations — Bracongo');
            $s->note('Généré le ' . $data['generated_at']->format('d/m/Y à H:i') . ' — CAN 2025 + Coupe du Monde 2026');
            $s->blank();

            $s->section('CHIFFRES CLÉS');
            $s->headerRow(['Indicateur', 'Valeur']);
            $s->row(['Total inscrits', $o['total_users']]);
            $s->row(['Inscrits actifs', $o['active_users']]);
            $s->row(['Inscrits inactifs', $o['inactive_users']]);
            $s->row(['Joueurs ayant pronostiqué', $o['users_with_pronostics']]);
            $s->row(['Taux de participation (%)', $o['participation_rate']]);
            $s->row(['Nombre total de matchs', $o['total_matches']]);
            $s->row(['Total pronostics', $o['total_pronostics']]);
            $s->row(['Total gagnants', $o['total_winners']]);
            $s->row(['Total perdants', $o['total_losers']]);
            $s->row(['Scores exacts', $o['total_exact']]);
            $s->row(['Points distribués', $o['total_points']]);
            $s->row(['Taux de réussite global (%)', $o['global_success_rate']]);
            $s->blank();

            $s->section('PAR COMPÉTITION');
            $s->headerRow(['Compétition', 'Matchs', 'Pronostics', 'Gagnants', 'Perdants', 'Scores exacts', 'Points', 'Taux réussite (%)']);
            foreach ($data['by_competition'] as $c) {
                $s->row([
                    $c['competition'], $c['matches'], $c['pronostics'], $c['winners'],
                    $c['losers'], $c['exact'], $c['points'], $c['success_rate'],
                ]);
            }
        });
    }

    private function sheetMatches(SimpleXlsxWriter $x, array $data): void
    {
        $x->addSheet('Matchs', function ($s) use ($data) {
            $s->title('Détail par match');
            $s->blank();
            $s->headerRow(['Compétition', 'Date', 'Match', 'Score', 'Statut', 'Pronostics', 'Gagnants', 'Perdants', 'Scores exacts', 'Points', 'Taux réussite (%)']);

            foreach ($data['matches'] as $m) {
                $s->row([
                    $m['competition'],
                    $m['date']->format('d/m/Y H:i'),
                    $m['label'],
                    $m['score'],
                    ucfirst($m['status']),
                    $m['total'],
                    $m['winners'],
                    $m['losers'],
                    $m['exact'],
                    $m['points'],
                    $m['success_rate'],
                ]);
            }
        });
    }

    private function sheetPlayers(SimpleXlsxWriter $x, array $data): void
    {
        $x->addSheet('Classement joueurs', function ($s) use ($data) {
            $s->title('Top joueurs (classement par points)');
            $s->blank();
            $s->headerRow(['#', 'Nom', 'Téléphone', 'Localité', 'Pronostics', 'Gagnés', 'Scores exacts', 'Points']);

            foreach ($data['top_players'] as $p) {
                $s->row([
                    $p['rank'],
                    $p['name'],
                    $p['phone'],
                    $p['address'] ?: '—',
                    $p['pronostics'],
                    $p['wins'],
                    $p['exact'],
                    $p['points'],
                ]);
            }
        });
    }

    private function sheetLocalities(SimpleXlsxWriter $x, array $data): void
    {
        $loc = $data['localities'];

        $x->addSheet('Localités', function ($s) use ($loc) {
            $s->title('Répartition géographique des inscrits');
            $s->note("Basé sur l'adresse saisie ; communes regroupées automatiquement.");
            $s->blank();
            $s->headerRow(['Commune / Localité', 'Inscrits']);

            foreach ($loc['rows'] as $r) {
                $s->row([$r['commune'], $r['count']]);
            }
            if ($loc['not_provided'] > 0) {
                $s->row(['Adresse non renseignée', $loc['not_provided']]);
            }
        });
    }

    private function sheetRegistrations(SimpleXlsxWriter $x, array $data): void
    {
        $reg = $data['registrations'];

        $x->addSheet('Inscrits', function ($s) use ($reg) {
            $s->title('Détail des inscrits');
            $s->blank();

            $s->section('PAR STATUT');
            $s->headerRow(['Statut', 'Nombre']);
            foreach ($reg['by_status'] as $r) {
                $s->row([$r['label'], $r['count']]);
            }
            $s->blank();

            $s->section('PAR SOURCE');
            $s->headerRow(['Source', 'Nombre']);
            foreach ($reg['by_source'] as $r) {
                $s->row([$r['label'], $r['count']]);
            }
            $s->blank();

            $s->section('ÉVOLUTION MENSUELLE');
            $s->headerRow(['Mois', "Nombre d'inscrits"]);
            foreach ($reg['by_month'] as $r) {
                $s->row([$r['label'], $r['count']]);
            }
        });
    }
}
