@extends('commercant.layouts.app')
@section('title', 'Accueil')

@section('content')

{{-- Alerte commandes libres --}}
@if($stats['unclaimed'] > 0)
<a href="{{ route('commercant.orders.index', ['status' => 'received']) }}"
   class="block bg-orange-500 rounded-2xl p-4 mb-4 shadow-sm active:bg-orange-600 transition-colors">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-white font-bold">{{ $stats['unclaimed'] }} commande(s) libre(s) 🔓</p>
            <p class="text-orange-100 text-sm mt-0.5">Non assignées — cliquez pour les prendre →</p>
        </div>
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">🙋</div>
    </div>
</a>
@endif

{{-- Stats globales --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-blue-500">
        <p class="text-xs text-gray-500 font-medium mb-1">Nouvelles</p>
        <p class="text-4xl font-bold text-gray-900">{{ $stats['received'] }}</p>
        <p class="text-xs text-blue-600 font-medium mt-1">à traiter</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-yellow-500">
        <p class="text-xs text-gray-500 font-medium mb-1">Préparation</p>
        <p class="text-4xl font-bold text-gray-900">{{ $stats['preparing'] }}</p>
        <p class="text-xs text-yellow-600 font-medium mt-1">en cours</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-500 font-medium mb-1">Prêtes</p>
        <p class="text-4xl font-bold text-gray-900">{{ $stats['ready'] }}</p>
        <p class="text-xs text-green-600 font-medium mt-1">à récupérer</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 border-yellow-700">
        <p class="text-xs text-gray-500 font-medium mb-1">CA total</p>
        <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['revenue'], 0) }}<span class="text-lg ml-1">$</span></p>
        <p class="text-xs text-gray-400 font-medium mt-1">{{ $stats['total'] }} commandes</p>
    </div>
</div>

{{-- Mes stats perso --}}
<div class="bg-gray-900 rounded-2xl p-4 mb-5">
    <p class="text-xs text-yellow-500 font-bold uppercase tracking-widest mb-3">Mes performances</p>
    <div class="grid grid-cols-3 gap-3">
        <div class="text-center">
            <p class="text-2xl font-black text-white">{{ $myStats['delivered'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Livrées</p>
        </div>
        <div class="text-center border-x border-white/10">
            <p class="text-2xl font-black text-white">{{ $myStats['today'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Auj.</p>
        </div>
        <div class="text-center">
            <p class="text-xl font-black text-yellow-400">{{ number_format($myStats['revenue'], 0) }}$</p>
            <p class="text-xs text-gray-400 mt-0.5">Mon CA</p>
        </div>
    </div>
</div>

{{-- Raccourci commandes en attente --}}
@if($stats['received'] > 0)
<a href="{{ route('commercant.orders.index', ['status' => 'received']) }}"
   class="block bg-blue-600 rounded-2xl p-4 mb-6 shadow-sm active:bg-blue-700 transition-colors">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-white font-bold text-lg">{{ $stats['received'] }} nouvelle(s) commande(s)</p>
            <p class="text-blue-200 text-sm mt-0.5">Appuyez pour traiter →</p>
        </div>
        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
    </div>
</a>
@endif

{{-- Commandes récentes --}}
<div class="mb-3 flex items-center justify-between">
    <h2 class="font-bold text-gray-800 text-base">Commandes récentes</h2>
    <a href="{{ route('commercant.orders.index') }}" class="text-sm text-yellow-700 font-semibold">Tout voir →</a>
</div>

@if($recentOrders->isEmpty())
<div class="bg-white rounded-2xl shadow-sm p-10 text-center">
    <div class="text-5xl mb-3">🍷</div>
    <p class="text-gray-500 font-medium">Aucune commande pour le moment.</p>
</div>
@else
<div class="space-y-3">
    @foreach($recentOrders as $order)
    <a href="{{ route('commercant.orders.show', $order->order_ref) }}"
       class="block bg-white rounded-2xl shadow-sm p-4 active:bg-gray-50 transition-colors border border-gray-50">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-mono font-bold text-gray-800 text-sm">{{ $order->order_ref }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0
                        @if($order->status === 'received') bg-blue-100 text-blue-700
                        @elseif($order->status === 'confirmed') bg-indigo-100 text-indigo-700
                        @elseif($order->status === 'preparing') bg-yellow-100 text-yellow-700
                        @elseif($order->status === 'ready') bg-green-100 text-green-700
                        @elseif($order->status === 'delivered') bg-gray-100 text-gray-600
                        @else bg-red-100 text-red-600 @endif">
                        {{ $order->status_label }}
                    </span>
                </div>
                <p class="text-gray-600 text-sm truncate">{{ $order->customer_name ?? $order->customer_phone }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ $order->created_at->diffForHumans() }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="font-bold text-gray-800">{{ number_format($order->total, 2) }} $</p>
                <p class="text-xs text-gray-400 mt-1">{{ $order->items->count() }} art.</p>
            </div>
        </div>
    </a>
    @endforeach
</div>
@endif

@endsection
