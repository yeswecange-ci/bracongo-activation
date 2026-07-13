@extends('admin.layouts.app')

@section('title', 'Rapport global')

@section('content')
@php $o = $data['overview']; @endphp
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">📑 Rapport global des activations</h1>
                <p class="text-gray-500 text-sm mt-1">CAN 2025 &amp; Coupe du Monde 2026 — toutes les statistiques.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.rapport.pdf') }}"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 font-medium">
                    <span>📄</span> Télécharger PDF
                </a>
                <a href="{{ route('admin.rapport.excel') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2 font-medium">
                    <span>📊</span> Télécharger Excel
                </a>
            </div>
        </div>

        {{-- KPI --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            @php
                $kpis = [
                    ['Inscrits', number_format($o['total_users'], 0, ',', ' '), 'text-blue-600'],
                    ['Matchs', $o['total_matches'], 'text-purple-600'],
                    ['Pronostics', number_format($o['total_pronostics'], 0, ',', ' '), 'text-green-600'],
                    ['Participation', $o['participation_rate'].'%', 'text-amber-600'],
                    ['Gagnants', number_format($o['total_winners'], 0, ',', ' '), 'text-green-700'],
                    ['Perdants', number_format($o['total_losers'], 0, ',', ' '), 'text-gray-600'],
                    ['Scores exacts', $o['total_exact'], 'text-amber-700'],
                    ['Réussite globale', $o['global_success_rate'].'%', 'text-emerald-600'],
                ];
            @endphp
            @foreach($kpis as [$label, $value, $color])
                <div class="bg-white rounded-lg shadow p-5 text-center">
                    <div class="text-3xl font-bold {{ $color }}">{{ $value }}</div>
                    <div class="text-gray-500 text-sm mt-1">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        {{-- Par compétition --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">🏆 Par compétition</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600 uppercase text-xs">
                            <th class="px-3 py-2">Compétition</th>
                            <th class="px-3 py-2 text-center">Matchs</th>
                            <th class="px-3 py-2 text-center">Pronostics</th>
                            <th class="px-3 py-2 text-center">Gagnants</th>
                            <th class="px-3 py-2 text-center">Perdants</th>
                            <th class="px-3 py-2 text-center">Scores exacts</th>
                            <th class="px-3 py-2 text-right">Réussite</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($data['by_competition'] as $c)
                            <tr>
                                <td class="px-3 py-2 font-semibold">{{ $c['competition'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $c['matches'] }}</td>
                                <td class="px-3 py-2 text-center">{{ number_format($c['pronostics'], 0, ',', ' ') }}</td>
                                <td class="px-3 py-2 text-center text-green-600 font-medium">{{ number_format($c['winners'], 0, ',', ' ') }}</td>
                                <td class="px-3 py-2 text-center">{{ number_format($c['losers'], 0, ',', ' ') }}</td>
                                <td class="px-3 py-2 text-center">{{ $c['exact'] }}</td>
                                <td class="px-3 py-2 text-right font-semibold">{{ $c['success_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Détail par match --}}
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">⚽ Détail par match</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600 uppercase text-xs">
                            <th class="px-3 py-2">Compét.</th>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Match</th>
                            <th class="px-3 py-2 text-center">Score</th>
                            <th class="px-3 py-2 text-center">Prono.</th>
                            <th class="px-3 py-2 text-center">Gagn.</th>
                            <th class="px-3 py-2 text-center">Perd.</th>
                            <th class="px-3 py-2 text-right">Réussite</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($data['matches'] as $m)
                            <tr>
                                <td class="px-3 py-2">
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $m['competition'] === \App\Services\StatsReportService::COMP_CDM ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $m['competition'] === \App\Services\StatsReportService::COMP_CDM ? 'CDM' : 'CAN' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">{{ $m['date']->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 font-medium">{{ $m['label'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $m['score'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $m['total'] }}</td>
                                <td class="px-3 py-2 text-center text-green-600">{{ $m['winners'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $m['losers'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $m['success_rate'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Localités + Inscrits --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">📍 Top localités</h2>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach(array_slice($data['localities']['rows'], 0, 15) as $i => $r)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span><span class="text-gray-400 mr-2">{{ $i + 1 }}.</span>{{ $r['commune'] }}</span>
                            <span class="font-bold text-blue-600">{{ number_format($r['count'], 0, ',', ' ') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">👥 Inscrits par source</h2>
                <div class="space-y-2">
                    @foreach($data['registrations']['by_source'] as $r)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="font-medium">{{ $r['label'] }}</span>
                            <span class="font-bold text-green-600">{{ number_format($r['count'], 0, ',', ' ') }}</span>
                        </div>
                    @endforeach
                </div>
                <h3 class="text-sm font-bold text-gray-500 uppercase mt-6 mb-2">Par statut</h3>
                <div class="space-y-2">
                    @foreach($data['registrations']['by_status'] as $r)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="font-medium">{{ $r['label'] }}</span>
                            <span class="font-bold text-gray-700">{{ number_format($r['count'], 0, ',', ' ') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
