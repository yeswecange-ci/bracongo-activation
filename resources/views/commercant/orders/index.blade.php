@extends('commercant.layouts.app')
@section('title', 'Commandes')

@section('content')

{{-- Filtres par statut (scroll horizontal) --}}
<div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 scrollbar-none" style="scrollbar-width:none">
    @foreach([
        'all'       => 'Toutes',
        'received'  => 'Reçues',
        'confirmed' => 'Confirmées',
        'preparing' => 'Préparation',
        'ready'     => 'Prêtes',
        'delivered' => 'Livrées',
        'cancelled' => 'Annulées',
    ] as $key => $label)
    <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => 1]) }}"
       class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition-colors whitespace-nowrap
           {{ request('status', 'all') === $key
               ? 'bg-yellow-600 text-white border-yellow-600'
               : 'bg-white text-gray-600 border-gray-200 hover:border-yellow-400' }}">
        {{ $label }}
        <span class="text-xs opacity-70 ml-0.5">({{ $counts[$key] }})</span>
    </a>
    @endforeach
</div>

{{-- Recherche --}}
<form method="GET" action="{{ route('commercant.orders.index') }}" class="flex gap-2 mb-5">
    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Référence, client, téléphone…"
           class="flex-1 bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 shadow-sm">
    <button type="submit"
            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-1.5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        Chercher
    </button>
</form>

{{-- Export CSV --}}
<div class="flex justify-end mb-4">
    <a href="{{ route('commercant.orders.export', request()->only(['status', 'date'])) }}"
       class="flex items-center gap-2 bg-white border border-gray-200 text-gray-600 px-4 py-2 rounded-xl text-sm font-medium shadow-sm transition-colors active:bg-gray-50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Exporter CSV
    </a>
</div>

{{-- Liste des commandes --}}
@if($orders->isEmpty())
<div class="bg-white rounded-2xl shadow-sm p-12 text-center">
    <div class="text-5xl mb-3">📋</div>
    <p class="text-gray-600 font-semibold">Aucune commande trouvée</p>
    <p class="text-gray-400 text-sm mt-1">Essayez de modifier vos filtres.</p>
</div>
@else
<div class="space-y-3">
    @foreach($orders as $order)
    <a href="{{ route('commercant.orders.show', $order->order_ref) }}"
       class="block bg-white rounded-2xl shadow-sm border border-gray-50 p-4 active:bg-yellow-50 transition-colors">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1.5">
                    <span class="font-mono font-bold text-gray-800">{{ $order->order_ref }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                        @if($order->status === 'received') bg-blue-100 text-blue-700
                        @elseif($order->status === 'confirmed') bg-indigo-100 text-indigo-700
                        @elseif($order->status === 'preparing') bg-yellow-100 text-yellow-700
                        @elseif($order->status === 'ready') bg-green-100 text-green-700
                        @elseif($order->status === 'delivered') bg-gray-100 text-gray-600
                        @else bg-red-100 text-red-600 @endif">
                        {{ $order->status_label }}
                    </span>
                </div>
                <p class="text-gray-700 text-sm font-medium truncate">{{ $order->customer_name ?? '—' }}</p>
                <p class="text-gray-400 text-xs mt-0.5">{{ $order->customer_phone }} · {{ $order->items->count() }} article(s)</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="font-bold text-gray-900 text-base">{{ number_format($order->total, 2) }} $</p>
                <p class="text-gray-400 text-xs mt-1">{{ $order->created_at->format('d/m H:i') }}</p>
            </div>
        </div>
        {{-- Indicator arrow --}}
        <div class="flex justify-end mt-2">
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
    @endforeach
</div>

{{-- Pagination --}}
@if($orders->hasPages())
<div class="mt-6">{{ $orders->links() }}</div>
@endif
@endif

@endsection
