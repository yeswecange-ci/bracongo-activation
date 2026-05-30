@extends('commercant.layouts.app')
@section('title', 'Accueil')

@section('content')

{{-- Greeting --}}
<div class="mb-6">
    <p class="text-xs font-semibold tracking-widest text-black/30 uppercase mb-1">Bonjour</p>
    <h1 class="text-2xl font-bold text-black/90">{{ auth('commercant')->user()->name }} 👋</h1>
</div>

{{-- Commandes libres --}}
@if($stats['unclaimed'] > 0)
<a href="{{ route('commercant.orders.index', ['status' => 'received']) }}"
   class="flex items-center justify-between p-4 rounded-2xl mb-5 transition-opacity active:opacity-80"
   style="background: linear-gradient(135deg, #C9A84C 0%, #A8873A 100%)">
    <div>
        <p class="text-black/80 text-xs font-semibold uppercase tracking-wide">Nouvelles commandes</p>
        <p class="text-black font-black text-2xl mt-0.5">{{ $stats['unclaimed'] }} libre{{ $stats['unclaimed'] > 1 ? 's' : '' }}</p>
        <p class="text-black/60 text-xs mt-1">Appuyez pour prendre →</p>
    </div>
    <div class="w-12 h-12 rounded-xl bg-black/10 flex items-center justify-center text-2xl flex-shrink-0">🙋</div>
</a>
@endif

{{-- Nouvelles commandes banner --}}
@if($stats['received'] > 0 && $stats['unclaimed'] == 0)
<a href="{{ route('commercant.orders.index', ['status' => 'received']) }}"
   class="flex items-center justify-between p-4 rounded-2xl mb-5 bg-black active:bg-black/80 transition-colors">
    <div>
        <p class="text-white/50 text-xs font-semibold uppercase tracking-wide">À traiter</p>
        <p class="text-white font-black text-2xl mt-0.5">{{ $stats['received'] }} commande{{ $stats['received'] > 1 ? 's' : '' }}</p>
    </div>
    <svg class="w-6 h-6 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
@endif

{{-- KPIs globaux --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="card p-4 text-center">
        <p class="text-2xl font-black text-black/90">{{ $stats['preparing'] }}</p>
        <p class="text-[11px] text-black/40 font-medium mt-1">En cours</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-black" style="color: #C9A84C">{{ $stats['ready'] }}</p>
        <p class="text-[11px] text-black/40 font-medium mt-1">Prêtes</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-black text-black/90">{{ $stats['total'] }}</p>
        <p class="text-[11px] text-black/40 font-medium mt-1">Total</p>
    </div>
</div>

{{-- Mes stats --}}
<div class="card mb-5 overflow-hidden">
    <div class="px-4 pt-4 pb-3 border-b border-black/5">
        <p class="text-xs font-bold tracking-widest uppercase text-black/30">Mes performances</p>
    </div>
    <div class="grid grid-cols-3 divide-x divide-black/5">
        <div class="p-4 text-center">
            <p class="text-xl font-black text-black/90">{{ $myStats['delivered'] }}</p>
            <p class="text-[11px] text-black/35 font-medium mt-1">Livrées</p>
        </div>
        <div class="p-4 text-center">
            <p class="text-xl font-black text-black/90">{{ $myStats['today'] }}</p>
            <p class="text-[11px] text-black/35 font-medium mt-1">Aujourd'hui</p>
        </div>
        <div class="p-4 text-center">
            <p class="text-xl font-black" style="color:#C9A84C">{{ number_format($myStats['revenue'], 0) }}<span class="text-sm">$</span></p>
            <p class="text-[11px] text-black/35 font-medium mt-1">Mon CA</p>
        </div>
    </div>
</div>

{{-- Commandes récentes --}}
<div class="flex items-center justify-between mb-3">
    <h2 class="text-sm font-bold text-black/70">Activité récente</h2>
    <a href="{{ route('commercant.orders.index') }}" class="text-xs font-semibold" style="color:#C9A84C">Tout voir</a>
</div>

@if($recentOrders->isEmpty())
<div class="card p-10 text-center">
    <p class="text-3xl mb-2">🍷</p>
    <p class="text-sm text-black/40 font-medium">Aucune commande pour le moment</p>
</div>
@else
<div class="space-y-2">
    @foreach($recentOrders as $order)
    @php
    $statusColor = match($order->status) {
        'received'  => '#2563EB',
        'confirmed' => '#7C3AED',
        'preparing' => '#D97706',
        'ready'     => '#16A34A',
        'delivered' => '#9CA3AF',
        default     => '#EF4444',
    };
    @endphp
    <a href="{{ route('commercant.orders.show', $order->order_ref) }}"
       class="card flex items-center gap-4 px-4 py-3.5 active:bg-black/2 transition-colors block">
        <div class="w-1.5 h-10 rounded-full flex-shrink-0" style="background: {{ $statusColor }}"></div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="font-mono text-xs font-bold text-black/70">{{ $order->order_ref }}</p>
                @if(!$order->commercant_id)
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700">Libre</span>
                @endif
            </div>
            <p class="text-sm font-medium text-black/90 truncate mt-0.5">{{ $order->customer_name ?? $order->customer_phone }}</p>
            <p class="text-xs text-black/35 mt-0.5">{{ $order->created_at->diffForHumans() }}</p>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-sm font-bold text-black/80">{{ number_format($order->total, 2) }} $</p>
            <p class="text-[11px] text-black/35 mt-0.5">{{ $order->items->count() }} art.</p>
        </div>
    </a>
    @endforeach
</div>
@endif

@endsection
