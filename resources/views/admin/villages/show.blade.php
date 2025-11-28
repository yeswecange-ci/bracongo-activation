@extends('admin.layouts.app')

@section('title', 'Détails du Village')
@section('page-title', $village->name)

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.villages.edit', $village) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
        <form action="{{ route('admin.villages.destroy', $village) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce village ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </form>
    </div>

    <!-- Informations du village -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h3>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Nom</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $village->name }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                <dd class="mt-1">
                    @if($village->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
                    @endif
                </dd>
            </div>

            <div class="md:col-span-2">
                <dt class="text-sm font-medium text-gray-500">Adresse</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $village->address }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Capacité maximale</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $village->capacity ?? 'Non défini' }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Inscrits actuels</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $village->users->count() }}</dd>
            </div>
        </dl>
    </div>

    <!-- Partenaires -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Partenaires ({{ $village->partners->count() }})</h3>

        @if($village->partners->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($village->partners as $partner)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <p class="font-medium text-gray-900">{{ $partner->name }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $partner->is_active ? 'Actif' : 'Inactif' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun partenaire associé à ce village</p>
        @endif
    </div>

    <!-- Inscrits récents -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Inscrits récents ({{ $village->users->count() }})</h3>

        @if($village->users->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Téléphone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($village->users->take(10) as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->phone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun inscrit pour ce village</p>
        @endif
    </div>
</div>
@endsection
