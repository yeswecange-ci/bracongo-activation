@extends('admin.layouts.app')

@section('title', 'Créer un Lot')
@section('page-title', 'Créer un Lot')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.prizes.store') }}" method="POST">
            @csrf

            <!-- Nom du lot -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du Lot <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: T-shirt officiel CAN, Bon d'achat 50$..."
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description (optionnel)
                </label>
                <textarea
                    name="description"
                    id="description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Description du lot..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Partenaire -->
            <div class="mb-6">
                <label for="partner_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Partenaire donateur (optionnel)
                </label>
                <select
                    name="partner_id"
                    id="partner_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('partner_id') border-red-500 @enderror"
                >
                    <option value="">-- Aucun partenaire --</option>
                    @foreach($partners as $partner)
                        <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                            {{ $partner->name }}
                        </option>
                    @endforeach
                </select>
                @error('partner_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Le partenaire qui offre ce lot</p>
            </div>

            <!-- Quantité -->
            <div class="mb-6">
                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                    Quantité disponible <span class="text-red-500">*</span>
                </label>
                <input
                    type="number"
                    name="quantity"
                    id="quantity"
                    value="{{ old('quantity', 1) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('quantity') border-red-500 @enderror"
                    placeholder="Ex: 100"
                    min="1"
                    required
                >
                @error('quantity')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Nombre total de lots disponibles</p>
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
                    <span class="ml-2 text-sm text-gray-700">Lot actif</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Les lots inactifs ne seront pas distribués</p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.prizes.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Créer le Lot
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
