@extends('admin.layouts.app')

@section('title', 'Créer un Village')
@section('page-title', 'Créer un Village CAN')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.villages.store') }}" method="POST">
            @csrf

            <!-- Nom du village -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du Village <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Village GOMBE"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Adresse -->
            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse complète <span class="text-red-500">*</span>
                </label>
                <textarea
                    name="address"
                    id="address"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror"
                    placeholder="Ex: Avenue de la Liberté, Commune de Gombe, Kinshasa"
                    required
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacité -->
            <div class="mb-6">
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                    Capacité maximale (optionnel)
                </label>
                <input
                    type="number"
                    name="capacity"
                    id="capacity"
                    value="{{ old('capacity') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('capacity') border-red-500 @enderror"
                    placeholder="Ex: 500"
                    min="1"
                >
                @error('capacity')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Nombre maximum de personnes que le village peut accueillir</p>
            </div>

            <!-- Statut actif -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        {{ old('is_active', true) ? 'checked' : '' }}
                    >
                    <span class="ml-2 text-sm text-gray-700">Village actif</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Les villages inactifs ne seront pas proposés aux joueurs</p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.villages.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Créer le Village
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
