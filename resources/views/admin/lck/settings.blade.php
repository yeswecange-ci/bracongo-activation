@extends('admin.layouts.app')
@section('title', 'LCK — Paramètres')
@section('page-title', 'La Clé des Châteaux — Paramètres')

@section('content')
<div class="max-w-2xl space-y-6">

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.lck.settings.update') }}"
          class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        @csrf

        <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="font-bold text-gray-800">📍 Point de retrait / Livraison</h2>
            <p class="text-sm text-gray-500 mt-1">Ces informations apparaissent dans la notification WhatsApp envoyée au client quand sa commande est prête.</p>
        </div>

        <div class="p-6 space-y-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom du point de retrait</label>
                <input type="text" name="pickup_name" value="{{ old('pickup_name', $settings['pickup_name'] ?? '') }}" required
                       placeholder="La Clé des Châteaux"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Adresse</label>
                <textarea name="pickup_address" rows="3" required
                          placeholder="Boulevard du 30 Juin&#10;Commune de Gombe"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 font-mono">{{ old('pickup_address', $settings['pickup_address'] ?? '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Une ligne par élément d'adresse.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ville / Pays</label>
                    <input type="text" name="pickup_city" value="{{ old('pickup_city', $settings['pickup_city'] ?? '') }}" required
                           placeholder="Kinshasa, RDC"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone</label>
                    <input type="text" name="pickup_phone" value="{{ old('pickup_phone', $settings['pickup_phone'] ?? '') }}"
                           placeholder="+243 XXX XXX XXX"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Horaires d'ouverture</label>
                    <input type="text" name="pickup_hours" value="{{ old('pickup_hours', $settings['pickup_hours'] ?? '') }}" required
                           placeholder="Lun–Sam  9h–19h"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Délai de retrait (jours ouvrés)
                        <span class="text-xs text-gray-400 font-normal ml-1">— après cet délai, commande annulée</span>
                    </label>
                    <input type="number" name="pickup_deadline" value="{{ old('pickup_deadline', $settings['pickup_deadline'] ?? 5) }}"
                           min="1" max="30" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
                </div>
            </div>

            {{-- Aperçu du message WhatsApp --}}
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Aperçu du message WhatsApp</p>
                <div class="bg-[#ECE5DD] rounded-xl p-4 text-sm font-mono text-gray-800 leading-relaxed whitespace-pre-line">📦 *Votre commande est prête !*

Référence : *LCK-2026-XXXX*
Total : *77.00 $*

🏪 {{ $settings['pickup_name'] ?? 'La Clé des Châteaux' }}
📍 {{ str_replace("\n", "\n   ", $settings['pickup_address'] ?? 'Boulevard du 30 Juin') }}
🌍 {{ $settings['pickup_city'] ?? 'Kinshasa, RDC' }}
@if(!empty($settings['pickup_phone']))📞 {{ $settings['pickup_phone'] }}@endif
🕐 {{ $settings['pickup_hours'] ?? 'Lun–Sam 9h–19h' }}

⚠️ Vous avez *{{ $settings['pickup_deadline'] ?? 5 }} jours ouvrés* pour récupérer votre colis.

📄 Votre bon de commande :
https://can-wabracongo.ywcdigital.com/lck/receipt/LCK-2026-XXXX?t=...</div>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white px-6 py-2.5 rounded-lg hover:from-yellow-700 hover:to-yellow-800 transition-all shadow-sm text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer
            </button>
        </div>
    </form>

</div>
@endsection
