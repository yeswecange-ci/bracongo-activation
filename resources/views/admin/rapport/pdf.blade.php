@php
    $o = $data['overview'];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport global des activations — Bracongo</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        @page { margin: 108px 30px 60px 30px; }
        body { margin: 0; color: #1f2937; font-size: 11px; }

        header {
            position: fixed;
            top: -88px; left: 0; right: 0; height: 78px;
        }
        .header-band {
            background-color: #0B6E4F;
            color: #ffffff;
            padding: 14px 22px;
            border-radius: 8px;
        }
        .header-band h1 { margin: 0; font-size: 18px; }
        .header-band .sub { margin-top: 3px; font-size: 10px; color: #c8f0df; }
        .header-band .brand { float: right; font-size: 11px; font-weight: bold; text-align: right; }

        footer {
            position: fixed;
            bottom: -42px; left: 0; right: 0; height: 34px;
            font-size: 9px; color: #6b7280;
            border-top: 1px solid #e5e7eb; padding-top: 6px;
        }
        footer .page:after { content: "Page " counter(page); }

        h2.section {
            font-size: 13px; color: #065f46;
            border-left: 4px solid #0B6E4F;
            padding: 4px 0 4px 8px; margin: 18px 0 10px 0;
        }

        /* Cartes de KPI */
        table.kpi { width: 100%; border-collapse: separate; border-spacing: 6px; margin-bottom: 6px; }
        table.kpi td {
            width: 25%; text-align: center;
            background-color: #f0fdf4; border: 1px solid #bbf7d0;
            border-radius: 6px; padding: 10px 6px;
        }
        table.kpi .num { font-size: 18px; font-weight: bold; color: #0B6E4F; }
        table.kpi .lbl { font-size: 8px; color: #4b5563; text-transform: uppercase; margin-top: 2px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.data thead th {
            background-color: #0B6E4F; color: #fff;
            font-size: 9px; text-transform: uppercase;
            padding: 7px 6px; text-align: left;
        }
        table.data tbody td {
            padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px;
        }
        table.data tbody tr:nth-child(even) { background-color: #f9fafb; }
        table.data td.r, table.data th.r { text-align: right; }
        table.data td.c, table.data th.c { text-align: center; }

        .comp-tag {
            display: inline-block; padding: 1px 6px; border-radius: 8px;
            font-size: 8px; font-weight: bold;
        }
        .comp-can { background-color: #fef3c7; color: #92400e; }
        .comp-cdm { background-color: #dbeafe; color: #1e40af; }
        .rank {
            display: inline-block; width: 16px; height: 16px;
            background-color: #0B6E4F; color: #fff;
            border-radius: 8px; text-align: center; font-size: 9px; font-weight: bold;
        }
        .muted { color: #9ca3af; font-style: italic; }
        .avoid-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    <header>
        <div class="header-band">
            <span class="brand">Bracongo<br>CAN 2025 · Coupe du Monde 2026</span>
            <h1>Rapport global des activations</h1>
            <div class="sub">Synthèse des matchs, pronostics, inscrits et localités</div>
        </div>
    </header>

    <footer>
        <span>Document généré le {{ $data['generated_at']->format('d/m/Y \à H:i') }}</span>
        <span class="page" style="float:right;"></span>
    </footer>

    <main>
        {{-- ===== VUE D'ENSEMBLE ===== --}}
        <h2 class="section">Vue d'ensemble</h2>
        <table class="kpi">
            <tr>
                <td><div class="num">{{ number_format($o['total_users'], 0, ',', ' ') }}</div><div class="lbl">Inscrits</div></td>
                <td><div class="num">{{ $o['total_matches'] }}</div><div class="lbl">Matchs</div></td>
                <td><div class="num">{{ number_format($o['total_pronostics'], 0, ',', ' ') }}</div><div class="lbl">Pronostics</div></td>
                <td><div class="num">{{ $o['participation_rate'] }}%</div><div class="lbl">Participation</div></td>
            </tr>
            <tr>
                <td><div class="num">{{ number_format($o['total_winners'], 0, ',', ' ') }}</div><div class="lbl">Gagnants</div></td>
                <td><div class="num">{{ number_format($o['total_losers'], 0, ',', ' ') }}</div><div class="lbl">Perdants</div></td>
                <td><div class="num">{{ $o['total_exact'] }}</div><div class="lbl">Scores exacts</div></td>
                <td><div class="num">{{ $o['global_success_rate'] }}%</div><div class="lbl">Réussite globale</div></td>
            </tr>
        </table>

        {{-- ===== PAR COMPÉTITION ===== --}}
        <h2 class="section">Par compétition</h2>
        <table class="data avoid-break">
            <thead>
                <tr>
                    <th>Compétition</th>
                    <th class="c">Matchs</th>
                    <th class="c">Pronostics</th>
                    <th class="c">Gagnants</th>
                    <th class="c">Perdants</th>
                    <th class="c">Sc. exacts</th>
                    <th class="c">Points</th>
                    <th class="r">Réussite</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['by_competition'] as $c)
                    <tr>
                        <td><strong>{{ $c['competition'] }}</strong></td>
                        <td class="c">{{ $c['matches'] }}</td>
                        <td class="c">{{ number_format($c['pronostics'], 0, ',', ' ') }}</td>
                        <td class="c">{{ number_format($c['winners'], 0, ',', ' ') }}</td>
                        <td class="c">{{ number_format($c['losers'], 0, ',', ' ') }}</td>
                        <td class="c">{{ $c['exact'] }}</td>
                        <td class="c">{{ number_format($c['points'], 0, ',', ' ') }}</td>
                        <td class="r">{{ $c['success_rate'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ===== DÉTAIL PAR MATCH ===== --}}
        <h2 class="section">Détail par match (gagnants / perdants)</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>Compét.</th>
                    <th>Date</th>
                    <th>Match</th>
                    <th class="c">Score</th>
                    <th class="c">Prono.</th>
                    <th class="c">Gagn.</th>
                    <th class="c">Perd.</th>
                    <th class="c">Exacts</th>
                    <th class="r">Réussite</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['matches'] as $m)
                    <tr>
                        <td>
                            <span class="comp-tag {{ $m['competition'] === \App\Services\StatsReportService::COMP_CDM ? 'comp-cdm' : 'comp-can' }}">
                                {{ $m['competition'] === \App\Services\StatsReportService::COMP_CDM ? 'CDM' : 'CAN' }}
                            </span>
                        </td>
                        <td>{{ $m['date']->format('d/m/Y') }}</td>
                        <td><strong>{{ $m['label'] }}</strong></td>
                        <td class="c">{{ $m['score'] }}</td>
                        <td class="c">{{ $m['total'] }}</td>
                        <td class="c">{{ $m['winners'] }}</td>
                        <td class="c">{{ $m['losers'] }}</td>
                        <td class="c">{{ $m['exact'] }}</td>
                        <td class="r">{{ $m['success_rate'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ===== INSCRITS ===== --}}
        <h2 class="section" style="page-break-before: always;">Inscrits</h2>
        <table style="width:100%;"><tr>
            <td style="width:49%; vertical-align:top; padding-right:8px;">
                <table class="data">
                    <thead><tr><th>Statut d'inscription</th><th class="r">Nombre</th></tr></thead>
                    <tbody>
                        @foreach($data['registrations']['by_status'] as $r)
                            <tr><td>{{ $r['label'] }}</td><td class="r">{{ number_format($r['count'], 0, ',', ' ') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
            <td style="width:49%; vertical-align:top; padding-left:8px;">
                <table class="data">
                    <thead><tr><th>Source d'acquisition</th><th class="r">Nombre</th></tr></thead>
                    <tbody>
                        @foreach($data['registrations']['by_source'] as $r)
                            <tr><td>{{ $r['label'] }}</td><td class="r">{{ number_format($r['count'], 0, ',', ' ') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr></table>

        <table class="data" style="margin-top:6px;">
            <thead><tr><th>Mois</th><th class="r">Nouveaux inscrits</th></tr></thead>
            <tbody>
                @foreach($data['registrations']['by_month'] as $r)
                    <tr><td>{{ $r['label'] }}</td><td class="r">{{ number_format($r['count'], 0, ',', ' ') }}</td></tr>
                @endforeach
            </tbody>
        </table>

        {{-- ===== LOCALITÉS ===== --}}
        <h2 class="section">Localités des inscrits</h2>
        <p class="muted" style="margin-top:-4px;">
            {{ number_format($data['localities']['total_geo'], 0, ',', ' ') }} inscrits avec adresse renseignée ·
            {{ number_format($data['localities']['not_provided'], 0, ',', ' ') }} sans adresse.
            Communes regroupées automatiquement.
        </p>
        <table class="data">
            <thead><tr><th>#</th><th>Commune / Localité</th><th class="r">Inscrits</th></tr></thead>
            <tbody>
                @foreach($data['localities']['rows'] as $i => $r)
                    <tr>
                        <td><span class="rank">{{ $i + 1 }}</span></td>
                        <td>{{ $r['commune'] }}</td>
                        <td class="r">{{ number_format($r['count'], 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
                @if($data['localities']['not_provided'] > 0)
                    <tr>
                        <td></td>
                        <td class="muted">Adresse non renseignée</td>
                        <td class="r">{{ number_format($data['localities']['not_provided'], 0, ',', ' ') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        {{-- ===== TOP JOUEURS ===== --}}
        <h2 class="section" style="page-break-before: always;">Top joueurs (classement par points)</h2>
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Localité</th>
                    <th class="c">Prono.</th>
                    <th class="c">Gagnés</th>
                    <th class="c">Exacts</th>
                    <th class="r">Points</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['top_players'] as $p)
                    <tr>
                        <td><span class="rank">{{ $p['rank'] }}</span></td>
                        <td><strong>{{ $p['name'] }}</strong></td>
                        <td>{{ $p['phone'] }}</td>
                        <td>{{ $p['address'] ?: '—' }}</td>
                        <td class="c">{{ $p['pronostics'] }}</td>
                        <td class="c">{{ $p['wins'] }}</td>
                        <td class="c">{{ $p['exact'] }}</td>
                        <td class="r"><strong>{{ $p['points'] }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted" style="text-align:center; padding:20px;">Aucun joueur avec des points pour le moment.</td></tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
