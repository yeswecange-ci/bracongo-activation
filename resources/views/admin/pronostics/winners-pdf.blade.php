<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gagnants - {{ $match->team_a }} vs {{ $match->team_b }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        @page { margin: 110px 32px 70px 32px; }
        body { margin: 0; color: #1f2937; font-size: 12px; }

        /* En-tête fixe */
        header {
            position: fixed;
            top: -90px; left: 0; right: 0;
            height: 80px;
        }
        .header-band {
            background-color: #0B6E4F;
            color: #ffffff;
            padding: 14px 24px;
            border-radius: 8px;
        }
        .header-band h1 { margin: 0; font-size: 18px; }
        .header-band .sub { margin-top: 3px; font-size: 11px; color: #c8f0df; }
        .header-band .brand { float: right; font-size: 11px; font-weight: bold; color: #ffffff; text-align: right; }

        /* Pied de page fixe */
        footer {
            position: fixed;
            bottom: -50px; left: 0; right: 0;
            height: 40px;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
        }
        footer .page:after { content: "Page " counter(page) " / " counter(pages); }

        /* Bandeau match */
        .match-card {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 14px;
        }
        .match-card table { width: 100%; }
        .match-teams { font-size: 16px; font-weight: bold; color: #065f46; }
        .match-score {
            font-size: 22px; font-weight: bold; color: #0B6E4F;
            text-align: center;
        }
        .match-meta { font-size: 10px; color: #4b5563; }

        /* Stats */
        .stats { width: 100%; margin-bottom: 14px; }
        .stats td {
            width: 33%;
            text-align: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 8px;
        }
        .stats .num { font-size: 18px; font-weight: bold; color: #0B6E4F; }
        .stats .lbl { font-size: 9px; color: #6b7280; text-transform: uppercase; }

        /* Tableau gagnants */
        table.winners { width: 100%; border-collapse: collapse; }
        table.winners thead th {
            background-color: #0B6E4F;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px 6px;
            text-align: left;
        }
        table.winners tbody td {
            padding: 7px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        table.winners tbody tr:nth-child(even) { background-color: #f9fafb; }
        .rank {
            display: inline-block;
            width: 18px; height: 18px;
            background-color: #0B6E4F; color: #fff;
            border-radius: 9px; text-align: center;
            font-size: 10px; font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-exact { background-color: #fef3c7; color: #92400e; }
        .badge-result { background-color: #dcfce7; color: #166534; }
        .pts { font-weight: bold; color: #0B6E4F; }
        .muted { color: #9ca3af; font-style: italic; }

        .empty {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-band">
            <span class="brand">CAN 2025<br>Bracongo</span>
            <h1>Liste des gagnants</h1>
            <div class="sub">{{ $match->team_a }} &nbsp;vs&nbsp; {{ $match->team_b }}</div>
        </div>
    </header>

    <footer>
        <span>Document genere le {{ $generatedAt->format('d/m/Y a H:i') }}</span>
        <span class="page" style="float:right;"></span>
    </footer>

    <main>
        <!-- Bandeau match -->
        <div class="match-card">
            <table>
                <tr>
                    <td style="width:42%;">
                        <div class="match-teams">{{ $match->team_a }}</div>
                        <div class="match-meta">Domicile</div>
                    </td>
                    <td style="width:16%;">
                        <div class="match-score">
                            {{ $match->score_a ?? '-' }} : {{ $match->score_b ?? '-' }}
                        </div>
                    </td>
                    <td style="width:42%; text-align:right;">
                        <div class="match-teams">{{ $match->team_b }}</div>
                        <div class="match-meta">Exterieur</div>
                    </td>
                </tr>
            </table>
            <div class="match-meta" style="margin-top:8px; text-align:center;">
                Match du {{ $match->match_date->format('d/m/Y a H:i') }}
                &nbsp;|&nbsp; Statut : {{ ucfirst($match->status) }}
            </div>
        </div>

        <!-- Stats -->
        <table class="stats">
            <tr>
                <td>
                    <div class="num">{{ $winners->count() }}</div>
                    <div class="lbl">Gagnants</div>
                </td>
                <td>
                    <div class="num">{{ $exactCount }}</div>
                    <div class="lbl">Scores exacts</div>
                </td>
                <td>
                    <div class="num">{{ $winners->sum('points_won') }}</div>
                    <div class="lbl">Points distribues</div>
                </td>
            </tr>
        </table>

        <!-- Tableau -->
        @if($winners->count() > 0)
            <table class="winners">
                <thead>
                    <tr>
                        <th style="width:6%;">#</th>
                        <th style="width:24%;">Nom</th>
                        <th style="width:20%;">Telephone</th>
                        <th style="width:24%;">Ville / Quartier</th>
                        <th style="width:14%;">Pronostic</th>
                        <th style="width:12%; text-align:center;">Gain</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($winners as $i => $winner)
                        <tr>
                            <td><span class="rank">{{ $i + 1 }}</span></td>
                            <td><strong>{{ $winner->user->name }}</strong></td>
                            <td>{{ $winner->user->phone }}</td>
                            <td>
                                @if($winner->user->address)
                                    {{ $winner->user->address }}
                                @elseif($winner->user->village)
                                    {{ $winner->user->village->name }}
                                @else
                                    <span class="muted">Non renseignee</span>
                                @endif
                            </td>
                            <td>{{ $winner->prediction_text }}</td>
                            <td style="text-align:center;">
                                @if($winner->points_won == \App\Models\Pronostic::POINTS_EXACT_SCORE)
                                    <span class="badge badge-exact">Score exact</span>
                                @else
                                    <span class="badge badge-result">Bon resultat</span>
                                @endif
                                <div class="pts" style="margin-top:3px;">{{ $winner->points_won }} pts</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">
                Aucun gagnant pour ce match.<br>
                <small>Les pronostics doivent etre evalues une fois le score final saisi.</small>
            </div>
        @endif
    </main>
</body>
</html>
