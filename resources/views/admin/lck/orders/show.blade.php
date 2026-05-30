@extends('admin.layouts.app')
@section('title', $order->order_ref)
@section('page-title', $order->order_ref)

@section('content')
<div class="space-y-6 max-w-2xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-3 text-sm">
        <a href="{{ route('admin.lck.orders.index') }}" class="text-yellow-600 hover:text-yellow-800 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Commandes
        </a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600 font-mono font-semibold">{{ $order->order_ref }}</span>
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
            @if($order->status==='received') bg-blue-100 text-blue-700
            @elseif($order->status==='preparing') bg-yellow-100 text-yellow-700
            @elseif($order->status==='ready') bg-green-100 text-green-700
            @elseif($order->status==='delivered') bg-gray-100 text-gray-600
            @else bg-red-100 text-red-600 @endif">
            {{ $order->status_label }}
        </span>
    </div>

    {{-- Détails commande --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="font-bold text-gray-800">Informations</h2>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Client</p>
                <p class="font-medium text-gray-900">{{ $order->customer_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Téléphone</p>
                <p class="font-medium text-gray-900">{{ $order->customer_phone }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Traitée par</p>
                <p class="font-medium text-gray-900">{{ $order->commercant?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Date</p>
                <p class="font-medium text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
            @if($order->customer_location)
            <div class="col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-yellow-600 text-xs uppercase tracking-wide mb-1 font-bold">📍 Zone / Adresse client</p>
                <p class="font-semibold text-gray-900">{{ $order->customer_location }}</p>
            </div>
            @endif
        </div>

        {{-- Articles --}}
        <div class="border-t border-gray-100">
            <div class="px-6 py-3 bg-gray-50">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Articles commandés</p>
            </div>
            @foreach($order->items as $item)
            <div class="flex justify-between items-center px-6 py-3 text-sm border-b border-gray-50 last:border-b-0">
                <span class="text-gray-800 font-medium">{{ $item->product_name }} <span class="text-gray-400 font-normal">× {{ $item->quantity }}</span></span>
                <span class="font-semibold text-gray-800">{{ number_format($item->subtotal, 2) }} $</span>
            </div>
            @endforeach
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 border-t border-gray-200">
                <span class="font-bold text-gray-700 uppercase text-sm">Total</span>
                <span class="font-bold text-xl text-gray-900">{{ number_format($order->total, 2) }} $</span>
            </div>
        </div>

        @if($order->notes)
        <div class="border-t border-gray-100 px-6 py-4">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Notes</p>
            <p class="text-gray-700 text-sm">{{ $order->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Supprimer --}}
    <form method="POST" action="{{ route('admin.lck.orders.destroy', $order->order_ref) }}"
          onsubmit="return confirm('Supprimer définitivement la commande {{ $order->order_ref }} ? Irréversible.')">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="inline-flex items-center gap-2 text-red-600 border border-red-200 hover:bg-red-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Supprimer cette commande
        </button>
    </form>
</div>
@endsection
