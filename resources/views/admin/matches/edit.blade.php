@extends('admin.layouts.app')

@section('title', 'Modifier le Match')
@section('page-title', 'Modifier le Match')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.matches.update', $match) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Équipe A -->
                <div>
                    <label for="team_a" class="block text-sm font-medium text-gray-700 mb-2">
                        Équipe A <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="team_a"
                        id="team_a"
                        value="{{ old('team_a', $match->team_a) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('team_a') border-red-500 @enderror"
                        placeholder="Ex: RDC"
                        required
                    >
                    @error('team_a')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Équipe B -->
                <div>
                    <label for="team_b" class="block text-sm font-medium text-gray-700 mb-2">
                        Équipe B <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="team_b"
                        id="team_b"
                        value="{{ old('team_b', $match->team_b) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('team_b') border-red-500 @enderror"
                        placeholder="Ex: Maroc"
                        required
                    >
                    @error('team_b')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Date et heure du match -->
            <div class="mt-6">
                <label for="match_date" class="block text-sm font-medium text-gray-700 mb-2">
                    Date et heure du match <span class="text-red-500">*</span>
                </label>
                <input
                    type="datetime-local"
                    name="match_date"
                    id="match_date"
                    value="{{ old('match_date', $match->match_date->format('Y-m-d\TH:i')) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('match_date') border-red-500 @enderror"
                    required
                >
                @error('match_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Scores -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="score_a" class="block text-sm font-medium text-gray-700 mb-2">
                        Score Équipe A
                    </label>
                    <input
                        type="number"
                        name="score_a"
                        id="score_a"
                        value="{{ old('score_a', $match->score_a) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('score_a') border-red-500 @enderror"
                        placeholder="0"
                        min="0"
                    >
                    @error('score_a')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="score_b" class="block text-sm font-medium text-gray-700 mb-2">
                        Score Équipe B
                    </label>
                    <input
                        type="number"
                        name="score_b"
                        id="score_b"
                        value="{{ old('score_b', $match->score_b) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('score_b') border-red-500 @enderror"
                        placeholder="0"
                        min="0"
                    >
                    @error('score_b')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Statut -->
            <div class="mt-6">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select
                    name="status"
                    id="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                    required
                >
                    <option value="scheduled" {{ old('status', $match->status) == 'scheduled' ? 'selected' : '' }}>Programmé</option>
                    <option value="live" {{ old('status', $match->status) == 'live' ? 'selected' : '' }}>En cours</option>
                    <option value="finished" {{ old('status', $match->status) == 'finished' ? 'selected' : '' }}>Terminé</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pronostics activés -->
            <div class="mt-6">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="pronostic_enabled"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        {{ old('pronostic_enabled', $match->pronostic_enabled) ? 'checked' : '' }}
                    >
                    <span class="ml-2 text-sm text-gray-700">Activer les pronostics pour ce match</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Les joueurs pourront faire des pronostics sur ce match</p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-4 mt-8">
                <a href="{{ route('admin.matches.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
