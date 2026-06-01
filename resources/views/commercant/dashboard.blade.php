@extends('commercant.layouts.app')
@section('title', 'Accueil')

@section('content')

{{-- Greeting --}}
<div class="mb-5">
    <p class="text-xs font-semibold tracking-widest text-black/30 uppercase mb-1">Bonjour</p>
    <h1 class="text-2xl font-bold text-black/90">{{ auth('commercant')->user()->name }} 👋</h1>
</div>

{{-- Activation notifications WhatsApp --}}
@php
    $botNumber = preg_replace('/^whatsapp:\+?/', '', config('services.twilio.whatsapp_from', '243841622222'));
    $waLink    = 'https://wa.me/' . $botNumber . '?text=VENDEUR';
@endphp
<a href="{{ $waLink }}" target="_blank"
   class="flex items-center gap-3 p-4 rounded-2xl mb-5 border transition-colors active:opacity-80"
   style="background: #F0FDF4; border-color: #BBF7D0">
    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-bold text-green-800">Activer les notifications WhatsApp</p>
        <p class="text-xs text-green-600 mt-0.5">Appuyez ici chaque jour pour recevoir les alertes commandes</p>
    </div>
    <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>

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
