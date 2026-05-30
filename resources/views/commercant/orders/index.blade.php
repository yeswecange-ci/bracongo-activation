@extends('commercant.layouts.app')
@section('title', 'Commandes')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-black text-black/90">Commandes</h1>
        <p class="text-xs text-black/35 mt-0.5">{{ $counts['all'] }} au total</p>
    </div>
    <a href="{{ route('commercant.orders.export', request()->only(['status', 'date'])) }}"
       class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-black/50 bg-white border border-black/8 active:bg-black/5 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        CSV
    </a>
</div>

{{-- Toggle Toutes / Mes commandes / Libres --}}
<div class="flex gap-2 mb-4">
    <a href="{{ request()->fullUrlWithQuery(['mine' => 0, 'page' => 1]) }}"
       class="flex-1 text-center py-2.5 rounded-xl text-xs font-bold transition-colors
           {{ !$mine ? 'bg-black text-white' : 'bg-white text-black/40 border border-black/8' }}">
        Toutes
    </a>
    <a href="{{ request()->fullUrlWithQuery(['mine' => 1, 'page' => 1]) }}"
       class="flex-1 text-center py-2.5 rounded-xl text-xs font-bold transition-colors
           {{ $mine ? 'bg-black text-white' : 'bg-white text-black/40 border border-black/8' }}">
        Les miennes
    </a>
    @if($counts['unclaimed'] > 0)
    <a href="{{ request()->fullUrlWithQuery(['mine' => 0, 'status' => 'received', 'page' => 1]) }}"
       class="flex-shrink-0 flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-bold transition-colors"
       style="background: #C9A84C; color: #000">
        🔓 {{ $counts['unclaimed'] }}
    </a>
    @endif
</div>

{{-- Filtres statut --}}
<div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 no-scrollbar">
    @foreach(['all' => 'Toutes', 'received' => 'Reçues', 'confirmed' => 'Confirmées', 'preparing' => 'Prépa.', 'ready' => 'Prêtes', 'delivered' => 'Livrées', 'cancelled' => 'Annulées'] as $key => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => 1]) }}"
       class="flex-shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold border transition-colors whitespace-nowrap
           {{ request('status', 'all') === $key
               ? 'bg-black text-white border-black'
               : 'bg-white text-black/50 border-black/8' }}">
        {{ $label }}
        <span class="opacity-50 ml-0.5">({{ $counts[$key] }})</span>
    </a>
    @endforeach
</div>

{{-- Recherche --}}
<form method="GET" action="{{ route('commercant.orders.index') }}" class="flex gap-2 mb-5">
    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
    <input type="hidden" name="mine" value="{{ request('mine', 0) }}">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Référence, client, téléphone…"
           class="flex-1 bg-white border border-black/8 rounded-xl px-4 py-2.5 text-sm focus:outline-none text-black/80 placeholder-black/25"
           style="font-family: inherit">
    <button type="submit"
            class="bg-black text-white px-4 py-2.5 rounded-xl text-sm font-semibold transition-opacity active:opacity-70">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </button>
</form>

{{-- Liste --}}
@if($orders->isEmpty())
<div class="card p-12 text-center">
    <p class="text-4xl mb-3">📋</p>
    <p class="text-sm font-semibold text-black/60">Aucune commande trouvée</p>
    <p class="text-xs text-black/30 mt-1">Essayez de modifier vos filtres</p>
</div>
@else
<div class="space-y-2">
    @foreach($orders as $order)
    @php
    $borderColor = match($order->status) {
        'received'  => '#2563EB',
        'confirmed' => '#7C3AED',
        'preparing' => '#D97706',
        'ready'     => '#16A34A',
        'delivered' => '#9CA3AF',
        default     => '#EF4444',
    };
    $statusLabel = match($order->status) {
        'received'  => 'Reçue',
        'confirmed' => 'Confirmée',
        'preparing' => 'Préparation',
        'ready'     => 'Prête',
        'delivered' => 'Livrée',
        default     => 'Annulée',
    };
    @endphp
    <a href="{{ route('commercant.orders.show', $order->order_ref) }}"
       class="card flex items-center gap-3 px-4 py-3.5 block active:scale-[0.99] transition-transform">
        <div class="w-1 h-12 rounded-full flex-shrink-0" style="background: {{ $borderColor }}"></div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono text-xs font-bold text-black/60">{{ $order->order_ref }}</span>
                @if(!$order->commercant_id && !in_array($order->status, ['delivered','cancelled']))
                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700 uppercase tracking-wide">Libre</span>
                @endif
            </div>
            <p class="text-sm font-semibold text-black/85 truncate">{{ $order->customer_name ?? '—' }}</p>
            <p class="text-xs text-black/35 mt-0.5">{{ $order->customer_phone }} · {{ $order->items->count() }} art. · {{ $order->created_at->format('d/m H:i') }}</p>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-base font-black text-black/85">{{ number_format($order->total, 2) }} $</p>
            <p class="text-[11px] font-semibold mt-1" style="color: {{ $borderColor }}">{{ $statusLabel }}</p>
        </div>
    </a>
    @endforeach
</div>

@if($orders->hasPages())
<div class="mt-5">{{ $orders->links() }}</div>
@endif
@endif

@endsection
