@extends('admin.layouts.app')

@section('title', 'Détails du Match')
@section('page-title', $match->team_a . ' vs ' . $match->team_b)

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.matches.edit', $match) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
        <form action="{{ route('admin.matches.destroy', $match) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </form>
    </div>

    <!-- Informations du match -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations du match</h3>

        <div class="flex items-center justify-center mb-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $match->team_a }}</div>
                @if($match->status === 'finished' && $match->score_a !== null)
                    <div class="text-5xl font-bold text-blue-600 mt-4">{{ $match->score_a }}</div>
                @endif
            </div>

            <div class="mx-8 text-2xl text-gray-400">vs</div>

            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $match->team_b }}</div>
                @if($match->status === 'finished' && $match->score_b !== null)
                    <div class="text-5xl font-bold text-blue-600 mt-4">{{ $match->score_b }}</div>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $match->match_date->format('d/m/Y à H:i') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                <dd class="mt-1">
                    @if($match->status === 'scheduled')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Programmé</span>
                    @elseif($match->status === 'live')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">En cours</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Terminé</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Pronostics</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $match->pronostic_enabled ? 'Activés' : 'Désactivés' }}
                </dd>
            </div>
        </dl>
    </div>

    <!-- Pronostics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pronostics ({{ $match->pronostics->count() }})</h3>

        @if($match->pronostics->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joueur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pronostic</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Résultat</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($match->pronostics as $pronostic)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pronostic->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $pronostic->user->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $pronostic->predicted_score_a }} - {{ $pronostic->predicted_score_b }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($pronostic->is_winner)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Gagnant</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Perdu</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $pronostic->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun pronostic pour ce match</p>
        @endif
    </div>

    <!-- Gagnants de lots -->
    @if($match->prizeWinners->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Gagnants de lots ({{ $match->prizeWinners->count() }})</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($match->prizeWinners as $winner)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $winner->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $winner->user->phone }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-blue-600">{{ $winner->prize->name }}</p>
                                @if($winner->collected_at)
                                    <span class="text-xs text-green-600">Récupéré</span>
                                @else
                                    <span class="text-xs text-orange-600">En attente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
