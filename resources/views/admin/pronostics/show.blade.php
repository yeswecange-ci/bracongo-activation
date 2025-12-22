@extends('admin.layouts.app')

@section('title', 'Détails du Pronostic')
@section('page-title', 'Pronostic #' . $pronostic->id)

@section('content')
<div class="space-y-6">
    <!-- Retour -->
    <div>
        <a href="{{ route('admin.pronostics.index') }}" class="text-blue-600 hover:text-blue-800">
            ← Retour à la liste
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations du pronostic -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations du Pronostic</h3>

            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Match</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <div class="font-bold">{{ $pronostic->match->team_a }} vs {{ $pronostic->match->team_b }}</div>
                        <div class="text-gray-500">{{ $pronostic->match->match_date->format('d/m/Y à H:i') }}</div>
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Pronostic</dt>
                    <dd class="mt-1">
                        <div class="text-2xl font-bold text-blue-600 mb-2">
                            {{ $pronostic->prediction_text }}
                        </div>
                        @if($pronostic->prediction_type)
                            <div class="mt-2">
                                @if($pronostic->prediction_type === 'team_a_win')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                        🏆 Victoire {{ $pronostic->match->team_a }}
                                    </span>
                                @elseif($pronostic->prediction_type === 'team_b_win')
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                        🏆 Victoire {{ $pronostic->match->team_b }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                                        🤝 Match nul
                                    </span>
                                @endif
                            </div>
                        @elseif($pronostic->predicted_score_a !== null && $pronostic->predicted_score_b !== null)
                            <div class="mt-2">
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-purple-100 text-purple-800">
                                    📊 Score exact
                                </span>
                            </div>
                        @endif
                    </dd>
                </div>

                @if($pronostic->match->status === 'finished')
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Résultat Final</dt>
                        <dd class="mt-1">
                            <span class="text-2xl font-bold text-green-600">
                                {{ $pronostic->match->score_a }} - {{ $pronostic->match->score_b }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1">
                            @if($pronostic->is_winner)
                                <span class="px-3 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                    ✅ Pronostic GAGNANT
                                </span>
                            @else
                                <span class="px-3 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                    ❌ Pronostic Perdant
                                </span>
                            @endif
                        </dd>
                    </div>
                @else
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut du match</dt>
                        <dd class="mt-1">
                            <span class="px-3 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                ⏳ Match en attente
                            </span>
                        </dd>
                    </div>
                @endif

                <div>
                    <dt class="text-sm font-medium text-gray-500">Date du pronostic</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $pronostic->created_at->format('d/m/Y à H:i') }}
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Informations de l'utilisateur -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Utilisateur</h3>

            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nom</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900">{{ $pronostic->user->name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $pronostic->user->phone }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Village</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($pronostic->user->village)
                            {{ $pronostic->user->village->name }}
                        @else
                            <span class="text-gray-400 italic">Non assigné</span>
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Total des pronostics</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $pronostic->user->pronostics->count() }} pronostic(s)
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Pronostics gagnants</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $pronostic->user->pronostics->where('is_winner', true)->count() }} gagné(s)
                    </dd>
                </div>

                <div class="pt-4">
                    <a href="{{ route('admin.users.show', $pronostic->user) }}" class="text-blue-600 hover:text-blue-800">
                        → Voir le profil complet
                    </a>
                </div>
            </dl>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>

        <div class="flex space-x-4">
            <form action="{{ route('admin.pronostics.destroy', $pronostic) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce pronostic ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Supprimer ce pronostic
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
