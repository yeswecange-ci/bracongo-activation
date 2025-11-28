@extends('admin.layouts.app')

@section('title', 'Créer un Partenaire')
@section('page-title', 'Créer un Partenaire')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.partners.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Nom du partenaire -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du Partenaire <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Ex: Coca-Cola, MTN, Airtel..."
                    required
                >
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Village associé -->
            <div class="mb-6">
                <label for="village_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Village associé (optionnel)
                </label>
                <select
                    name="village_id"
                    id="village_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('village_id') border-red-500 @enderror"
                >
                    <option value="">-- Aucun village --</option>
                    @foreach($villages as $village)
                        <option value="{{ $village->id }}" {{ old('village_id') == $village->id ? 'selected' : '' }}>
                            {{ $village->name }}
                        </option>
                    @endforeach
                </select>
                @error('village_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Le partenaire sera associé à ce village</p>
            </div>

            <!-- Logo -->
            <div class="mb-6">
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    Logo du Partenaire (optionnel)
                </label>
                <input
                    type="file"
                    name="logo"
                    id="logo"
                    accept="image/*"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('logo') border-red-500 @enderror"
                >
                @error('logo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Formats acceptés : JPG, PNG, GIF (max 2Mo)</p>
            </div>

            <!-- Aperçu du logo -->
            <div class="mb-6" id="preview-container" style="display: none;">
                <label class="block text-sm font-medium text-gray-700 mb-2">Aperçu</label>
                <img id="preview-image" src="" alt="Aperçu" class="h-32 w-32 object-cover rounded border border-gray-300">
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
                    <span class="ml-2 text-sm text-gray-700">Partenaire actif</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Les partenaires inactifs ne seront pas affichés</p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.partners.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Créer le Partenaire
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Aperçu du logo
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('preview-container').style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
