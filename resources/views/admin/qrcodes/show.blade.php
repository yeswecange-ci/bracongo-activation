@extends('admin.layouts.app')

@section('title', 'Détails du QR Code')
@section('page-title', 'QR Code - ' . $qrcode->source)

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.qrcodes.download', $qrcode) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Télécharger
        </a>
        <a href="{{ route('admin.qrcodes.edit', $qrcode) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
        <form action="{{ route('admin.qrcodes.destroy', $qrcode) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce QR Code ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- QR Code Image -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">QR Code</h3>

            @if($qrcode->qr_image_path)
                <div class="flex justify-center">
                    <img src="{{ asset('storage/' . $qrcode->qr_image_path) }}" alt="QR Code" class="w-full max-w-md border border-gray-200 rounded">
                </div>
            @else
                <div class="flex justify-center items-center h-64 bg-gray-100 rounded">
                    <p class="text-gray-500">Image non disponible</p>
                </div>
            @endif
        </div>

        <!-- Informations -->
        <div class="space-y-6">
            <!-- Informations générales -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h3>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Code</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900">{{ $qrcode->code }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Source / Emplacement</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $qrcode->source }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Statut</dt>
                        <dd class="mt-1">
                            @if($qrcode->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $qrcode->created_at->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Statistiques -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistiques</h3>

                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Nombre de scans</p>
                        <p class="text-3xl font-bold text-blue-900 mt-1">{{ $qrcode->scan_count }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions d'utilisation -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Instructions d'utilisation</h3>

        <div class="prose prose-sm max-w-none">
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Téléchargez l'image du QR Code en cliquant sur le bouton "Télécharger"</li>
                <li>Imprimez le QR Code ou intégrez-le dans vos supports visuels</li>
                <li>Placez le QR Code à l'emplacement indiqué : <strong>{{ $qrcode->source }}</strong></li>
                <li>Les utilisateurs scanneront le QR Code pour s'inscrire via WhatsApp</li>
                <li>Les scans seront automatiquement comptabilisés dans le backoffice</li>
            </ol>
        </div>
    </div>
</div>
@endsection
