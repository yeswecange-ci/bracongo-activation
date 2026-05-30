@extends('commercant.layouts.app')
@section('title', $order->order_ref)

@section('content')

{{-- Retour --}}
<a href="{{ route('commercant.orders.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 font-medium mb-4 active:text-gray-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Retour aux commandes
</a>

{{-- En-tête commande --}}
<div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div>
            <p class="font-mono font-bold text-gray-900 text-xl">{{ $order->order_ref }}</p>
            <p class="text-sm text-gray-400 mt-0.5">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-sm font-bold flex-shrink-0
            @if($order->status === 'received') bg-blue-100 text-blue-700
            @elseif($order->status === 'confirmed') bg-indigo-100 text-indigo-700
            @elseif($order->status === 'preparing') bg-yellow-100 text-yellow-700
            @elseif($order->status === 'ready') bg-green-100 text-green-700
            @elseif($order->status === 'delivered') bg-gray-100 text-gray-600
            @else bg-red-100 text-red-600 @endif">
            {{ $order->status_label }}
        </span>
    </div>

    {{-- Infos client --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-400 font-medium mb-0.5">Client</p>
            <p class="font-semibold text-gray-800 text-sm">{{ $order->customer_name ?? '—' }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-400 font-medium mb-0.5">Téléphone</p>
            <p class="font-semibold text-gray-800 text-sm">{{ $order->customer_phone }}</p>
        </div>
        @if($order->customer_location)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 col-span-2">
            <p class="text-xs text-yellow-600 font-medium mb-0.5">📍 Zone / Adresse</p>
            <p class="font-semibold text-gray-800 text-sm">{{ $order->customer_location }}</p>
        </div>
        @endif
        @if($order->commercant)
        <div class="bg-gray-50 rounded-xl p-3 col-span-2">
            <p class="text-xs text-gray-400 font-medium mb-0.5">Traitée par</p>
            <p class="font-semibold text-gray-800 text-sm">{{ $order->commercant->name }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Réclamation commande --}}
@php $myId = auth('commercant')->id(); @endphp
@if(!$order->commercant_id && !in_array($order->status, ['delivered','cancelled']))
<form method="POST" action="{{ route('commercant.orders.claim', $order->order_ref) }}" class="mb-4">
    @csrf
    <button type="submit"
        class="w-full py-4 rounded-2xl font-bold text-base text-white bg-orange-500 shadow-md active:opacity-90 transition-opacity">
        🙋 Je prends cette commande
    </button>
</form>
@elseif($order->commercant_id && $order->commercant_id !== $myId)
<div class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-2xl flex items-center gap-3">
    <span class="text-2xl">⚠️</span>
    <div>
        <p class="text-sm font-bold text-orange-700">Commande déjà prise</p>
        <p class="text-xs text-orange-600 mt-0.5">Traitée par <strong>{{ $order->commercant->name }}</strong></p>
    </div>
</div>
@endif

{{-- Bouton action principal (sticky en haut si pas encore terminé) --}}
@if(!in_array($order->status, ['delivered', 'cancelled']))
@php
$nextAction = match($order->status) {
    'received'  => ['value' => 'confirmed', 'label' => 'Confirmer la commande', 'color' => 'indigo', 'emoji' => '✅'],
    'confirmed' => ['value' => 'preparing', 'label' => 'Mettre en préparation', 'color' => 'yellow', 'emoji' => '🔧'],
    'preparing' => ['value' => 'ready',     'label' => 'Commande prête !',       'color' => 'green',  'emoji' => '📦'],
    'ready'     => ['value' => 'delivered', 'label' => 'Marquer comme livrée',   'color' => 'gray',   'emoji' => '🤝'],
    default     => null,
};
@endphp

@if($nextAction)
<form method="POST" action="{{ route('commercant.orders.status', $order->order_ref) }}" class="mb-4" id="main-status-form">
    @csrf
    <input type="hidden" name="status" value="{{ $nextAction['value'] }}">
    <textarea name="notes" rows="2" id="notes-field"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-yellow-500 bg-white shadow-sm"
        placeholder="Note interne (optionnelle)…">{{ old('notes', $order->notes) }}</textarea>
    <button type="submit"
        class="w-full py-4 rounded-2xl font-bold text-base text-white shadow-md active:opacity-90 transition-opacity
            @if($nextAction['color'] === 'green') bg-green-600
            @elseif($nextAction['color'] === 'yellow') bg-yellow-500
            @elseif($nextAction['color'] === 'indigo') bg-indigo-600
            @else bg-gray-600 @endif">
        {{ $nextAction['emoji'] }} {{ $nextAction['label'] }}
    </button>
</form>
@endif

{{-- Refus / Annulation avec motif --}}
<div class="mb-4">
    <button onclick="document.getElementById('refus-panel').classList.toggle('hidden')"
        class="w-full py-3 rounded-2xl font-semibold text-sm text-red-600 border-2 border-red-200 bg-white active:bg-red-50 transition-colors">
        ❌ Refuser / Annuler la commande
    </button>
    <div id="refus-panel" class="hidden mt-3 bg-red-50 border border-red-200 rounded-2xl p-4">
        <p class="text-xs font-bold text-red-600 mb-2 uppercase tracking-wide">Motif (obligatoire)</p>
        <form method="POST" action="{{ route('commercant.orders.status', $order->order_ref) }}"
              onsubmit="return document.getElementById('refus-notes').value.trim().length >= 5 || (alert('Indiquez un motif (min. 5 caractères)'), false)">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <div class="grid grid-cols-2 gap-2 mb-3">
                @foreach(['Rupture de stock', 'Zone non couverte', 'Client injoignable', 'Commande incorrecte'] as $motif)
                <button type="button" onclick="document.getElementById('refus-notes').value='{{ $motif }}'"
                    class="text-xs px-3 py-2 rounded-xl bg-white border border-red-200 text-red-600 active:bg-red-100 text-left transition-colors">
                    {{ $motif }}
                </button>
                @endforeach
            </div>
            <textarea id="refus-notes" name="notes" rows="2" required minlength="5"
                class="w-full border border-red-200 rounded-xl px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-red-400 bg-white"
                placeholder="Précisez le motif…">{{ old('notes') }}</textarea>
            <button type="submit"
                class="w-full py-3 rounded-xl font-bold text-sm text-white bg-red-500 active:bg-red-600 transition-colors">
                Confirmer le refus
            </button>
        </form>
    </div>
</div>
@endif

{{-- Contact client WhatsApp --}}
<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}"
   target="_blank"
   class="flex items-center justify-center gap-3 w-full py-4 rounded-2xl bg-green-500 text-white font-bold text-base shadow-md active:bg-green-600 transition-colors mb-4">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    Contacter via WhatsApp
</a>

{{-- Articles commandés --}}
<div class="bg-white rounded-2xl shadow-sm mb-4 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-800">Articles commandés</h3>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($order->items as $item)
        <div class="px-5 py-4 flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm">{{ $item->product_name }}</p>
                @if($item->product_category)
                <p class="text-xs text-gray-400 mt-0.5">{{ $item->product_category }}</p>
                @endif
                <p class="text-xs text-gray-500 mt-0.5">{{ number_format($item->unit_price, 2) }} $ × {{ $item->quantity }}</p>
            </div>
            <p class="font-bold text-gray-900 text-base flex-shrink-0">{{ number_format($item->subtotal, 2) }} $</p>
        </div>
        @endforeach
    </div>
    <div class="px-5 py-4 bg-gray-50 flex items-center justify-between">
        <span class="font-bold text-gray-700 uppercase text-sm tracking-wide">Total</span>
        <span class="font-black text-2xl text-gray-900">{{ number_format($order->total, 2) }} $</span>
    </div>
</div>

{{-- Supprimer la commande --}}
@if(in_array($order->status, ['cancelled', 'delivered']))
<form method="POST" action="{{ route('commercant.orders.destroy', $order->order_ref) }}" class="mb-4"
      onsubmit="return confirm('Supprimer définitivement cette commande ? Cette action est irréversible.')">
    @csrf
    @method('DELETE')
    <button type="submit"
        class="w-full py-3 rounded-2xl font-semibold text-xs text-gray-400 border border-gray-200 bg-white active:bg-gray-50 transition-colors">
        🗑 Supprimer cette commande
    </button>
</form>
@endif

{{-- Notes --}}
@if($order->notes)
<div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
    <h3 class="font-bold text-gray-800 mb-2 text-sm">Notes</h3>
    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $order->notes }}</p>
</div>
@endif

{{-- Historique --}}
<div class="bg-white rounded-2xl shadow-sm p-5">
    <h3 class="font-bold text-gray-800 mb-4 text-sm">Historique</h3>
    <div class="space-y-3">
        <div class="flex items-center gap-3 text-sm">
            <div class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></div>
            <span class="text-gray-500 w-24 flex-shrink-0">Reçue</span>
            <span class="text-gray-700 font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($order->confirmed_at)
        <div class="flex items-center gap-3 text-sm">
            <div class="w-3 h-3 rounded-full bg-indigo-500 flex-shrink-0"></div>
            <span class="text-gray-500 w-24 flex-shrink-0">Confirmée</span>
            <span class="text-gray-700 font-medium">{{ $order->confirmed_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($order->ready_at)
        <div class="flex items-center gap-3 text-sm">
            <div class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></div>
            <span class="text-gray-500 w-24 flex-shrink-0">Prête</span>
            <span class="text-gray-700 font-medium">{{ $order->ready_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($order->delivered_at)
        <div class="flex items-center gap-3 text-sm">
            <div class="w-3 h-3 rounded-full bg-gray-400 flex-shrink-0"></div>
            <span class="text-gray-500 w-24 flex-shrink-0">Livrée</span>
            <span class="text-gray-700 font-medium">{{ $order->delivered_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
    </div>
</div>

@endsection
