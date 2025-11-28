@extends('admin.layouts.app')

@section('title', 'Détails du Partenaire')
@section('page-title', $partner->name)

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.partners.edit', $partner) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
        <form action="{{ route('admin.partners.destroy', $partner) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </form>
    </div>

    <!-- Informations du partenaire -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h3>

        <div class="flex items-start space-x-6">
            <!-- Logo -->
            <div>
                @if($partner->logo)
                    <img src="{{ asset('storage/' . $partner->logo) }}" alt="{{ $partner->name }}" class="h-32 w-32 object-cover rounded border border-gray-300">
                @else
                    <div class="h-32 w-32 rounded border border-gray-300 bg-gray-100 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Détails -->
            <div class="flex-1">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $partner->name }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1">
                            @if($partner->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Village associé</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($partner->village)
                                <a href="{{ route('admin.villages.show', $partner->village) }}" class="text-blue-600 hover:underline">
                                    {{ $partner->village->name }}
                                </a>
                            @else
                                <span class="text-gray-500">Non assigné</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre de lots</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $partner->prizes->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Lots du partenaire -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Lots proposés ({{ $partner->prizes->count() }})</h3>

        @if($partner->prizes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($partner->prizes as $prize)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">{{ $prize->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $prize->description }}</p>
                        <div class="mt-2 flex items-center justify-between text-sm">
                            <span class="text-gray-600">Quantité: {{ $prize->quantity }}</span>
                            <span class="text-gray-600">Distribués: {{ $prize->distributed_count }}</span>
                        </div>
                        <div class="mt-2">
                            @if($prize->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun lot proposé par ce partenaire</p>
        @endif
    </div>
</div>
@endsection
