@extends('commercant.layouts.app')
@section('title', $order->order_ref)

@section('content')
@php $myId = auth('commercant')->id(); @endphp

{{-- Retour --}}
<a href="{{ route('commercant.orders.index') }}"
   class="inline-flex items-center gap-1.5 text-xs font-semibold text-black/40 mb-5 active:text-black/70 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
    </svg>
    Commandes
</a>

{{-- En-tête --}}
<div class="card p-5 mb-3">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div>
            <p class="font-mono font-black text-black/85 text-xl tracking-tight">{{ $order->order_ref }}</p>
            <p class="text-xs text-black/35 mt-1">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        @php
        $sc = match($order->status) {
            'received'  => ['bg-blue-50 text-blue-700', 'Reçue'],
            'confirmed' => ['bg-violet-50 text-violet-700', 'Confirmée'],
            'preparing' => ['bg-amber-50 text-amber-700', 'Préparation'],
            'ready'     => ['bg-green-50 text-green-700', 'Prête'],
            'delivered' => ['bg-gray-100 text-gray-500', 'Livrée'],
            default     => ['bg-red-50 text-red-600', 'Annulée'],
        };
        @endphp
        <span class="px-3 py-1.5 rounded-full text-xs font-bold flex-shrink-0 {{ $sc[0] }}">{{ $sc[1] }}</span>
    </div>

    {{-- Infos client --}}
    <div class="space-y-2">
        <div class="flex justify-between items-center py-2.5 border-b border-black/5 last:border-0">
            <span class="text-xs text-black/35 font-medium">Client</span>
            <span class="text-sm font-semibold text-black/80">{{ $order->customer_name ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-center py-2.5 border-b border-black/5">
            <span class="text-xs text-black/35 font-medium">Téléphone</span>
            <span class="text-sm font-semibold text-black/80">{{ $order->customer_phone }}</span>
        </div>
        @if($order->customer_location)
        <div class="flex justify-between items-center py-2.5 border-b border-black/5">
            <span class="text-xs text-black/35 font-medium">📍 Zone</span>
            <span class="text-sm font-semibold text-black/80">{{ $order->customer_location }}</span>
        </div>
        @endif
        <div class="flex justify-between items-center py-2.5 border-b border-black/5">
            <span class="text-xs text-black/35 font-medium">Paiement</span>
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold text-black/80">{{ $order->payment_method_label }}</span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $order->payment_status_label }}
                </span>
            </div>
        </div>
        @if($order->commercant)
        <div class="flex justify-between items-center py-2.5">
            <span class="text-xs text-black/35 font-medium">Traitée par</span>
            <span class="text-sm font-semibold text-black/80">{{ $order->commercant->name }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Réclamation --}}
@if(!$order->commercant_id && !in_array($order->status, ['delivered','cancelled']))
<form method="POST" action="{{ route('commercant.orders.claim', $order->order_ref) }}" class="mb-3">
    @csrf
    <button type="submit"
        class="w-full py-4 rounded-2xl font-bold text-sm text-white transition-opacity active:opacity-80"
        style="background: linear-gradient(135deg, #C9A84C, #A8873A)">
        🙋 Prendre cette commande
    </button>
</form>
@elseif($order->commercant_id && $order->commercant_id !== $myId)
<div class="flex items-center gap-3 p-4 rounded-2xl mb-3 bg-amber-50 border border-amber-200">
    <span class="text-xl">⚠️</span>
    <div>
        <p class="text-sm font-bold text-amber-800">Déjà prise par {{ $order->commercant->name }}</p>
        <p class="text-xs text-amber-600 mt-0.5">Vous pouvez consulter mais pas modifier</p>
    </div>
</div>
@endif

{{-- Action principale --}}
@if(!in_array($order->status, ['delivered', 'cancelled']))
@php
$actions = [
    'received'  => ['value' => 'confirmed', 'label' => 'Confirmer la commande', 'style' => 'background:#1A1A1A'],
    'confirmed' => ['value' => 'preparing', 'label' => 'Mettre en préparation', 'style' => 'background:#D97706'],
    'preparing' => ['value' => 'ready',     'label' => 'Commande prête !',       'style' => 'background:#16A34A'],
    'ready'     => ['value' => 'delivered', 'label' => 'Marquer comme livrée',   'style' => 'background:#374151'],
];
$action = $actions[$order->status] ?? null;
@endphp

@if($action)
<form method="POST" action="{{ route('commercant.orders.status', $order->order_ref) }}" class="mb-3">
    @csrf
    <input type="hidden" name="status" value="{{ $action['value'] }}">
    <textarea name="notes" rows="2"
        class="w-full border border-black/8 rounded-xl px-4 py-3 text-sm mb-3 focus:outline-none bg-white text-black/70 placeholder-black/25"
        placeholder="Note interne (optionnelle)…">{{ old('notes', $order->notes) }}</textarea>
    <button type="submit"
        class="w-full py-4 rounded-2xl font-bold text-sm text-white transition-opacity active:opacity-80"
        style="{{ $action['style'] }}">
        {{ $action['label'] }}
    </button>
</form>
@endif

{{-- Refus / Annulation --}}
<div class="mb-3">
    <button onclick="document.getElementById('refus').classList.toggle('hidden')"
        class="w-full py-3 rounded-2xl text-sm font-semibold text-red-500 bg-white border border-red-100 active:bg-red-50 transition-colors">
        Refuser / Annuler
    </button>
    <div id="refus" class="hidden mt-2 bg-red-50 border border-red-100 rounded-2xl p-4">
        <p class="text-xs font-bold text-red-500 uppercase tracking-wide mb-3">Motif</p>
        <form method="POST" action="{{ route('commercant.orders.status', $order->order_ref) }}"
              onsubmit="return document.getElementById('refus-notes').value.trim().length >= 5 || (alert('Motif requis'), false)">
            @csrf
            <input type="hidden" name="status" value="cancelled">
            <div class="grid grid-cols-2 gap-2 mb-3">
                @foreach(['Rupture de stock', 'Zone non couverte', 'Client injoignable', 'Commande incorrecte'] as $m)
                <button type="button" onclick="document.getElementById('refus-notes').value='{{ $m }}'"
                    class="text-xs px-3 py-2 rounded-xl bg-white border border-red-100 text-red-500 active:bg-red-50 text-left">{{ $m }}</button>
                @endforeach
            </div>
            <textarea id="refus-notes" name="notes" rows="2" required minlength="5"
                class="w-full border border-red-200 rounded-xl px-3 py-2 text-sm mb-3 focus:outline-none bg-white text-black/70"
                placeholder="Précisez…"></textarea>
            <button type="submit" class="w-full py-3 rounded-xl font-bold text-sm text-white bg-red-500 active:bg-red-600">
                Confirmer le refus
            </button>
        </form>
    </div>
</div>
@endif

{{-- Actions rapides --}}
<div class="grid grid-cols-2 gap-2 mb-3">
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $order->customer_phone) }}" target="_blank"
       class="flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-green-500 text-white font-semibold text-sm active:bg-green-600 transition-colors">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        WhatsApp
    </a>
    <a href="{{ route('commercant.orders.print', $order->order_ref) }}" target="_blank"
       class="flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-white border border-black/8 text-black/60 font-semibold text-sm active:bg-black/5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimer
    </a>
</div>

{{-- Articles --}}
<div class="card mb-3 overflow-hidden">
    <div class="px-4 py-3.5 border-b border-black/5">
        <p class="text-xs font-bold tracking-widest uppercase text-black/30">Articles commandés</p>
    </div>
    <div class="divide-y divide-black/5">
        @foreach($order->items as $item)
        <div class="px-4 py-3.5 flex items-center justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-black/85">{{ $item->product_name }}</p>
                @if($item->product_category)
                <p class="text-xs text-black/30 mt-0.5">{{ $item->product_category }}</p>
                @endif
                <p class="text-xs text-black/40 mt-0.5">{{ number_format($item->unit_price, 2) }} $ × {{ $item->quantity }}</p>
            </div>
            <p class="font-bold text-black/85 flex-shrink-0">{{ number_format($item->subtotal, 2) }} $</p>
        </div>
        @endforeach
    </div>
    <div class="px-4 py-4 bg-black/2 flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-widest text-black/40">Total</span>
        <span class="text-2xl font-black text-black/90">{{ number_format($order->total, 2) }} $</span>
    </div>
</div>

{{-- Notes --}}
@if($order->notes)
<div class="card p-4 mb-3">
    <p class="text-xs font-bold tracking-widest uppercase text-black/30 mb-2">Notes</p>
    <p class="text-sm text-black/60 whitespace-pre-line">{{ $order->notes }}</p>
</div>
@endif

{{-- Historique --}}
<div class="card p-4 mb-3">
    <p class="text-xs font-bold tracking-widest uppercase text-black/30 mb-4">Historique</p>
    <div class="space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
            <span class="text-xs text-black/40 w-20">Reçue</span>
            <span class="text-xs font-semibold text-black/70">{{ $order->created_at->format('d/m/Y H:i') }}</span>
        </div>
        @if($order->confirmed_at)
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-violet-500 flex-shrink-0"></div>
            <span class="text-xs text-black/40 w-20">Confirmée</span>
            <span class="text-xs font-semibold text-black/70">{{ $order->confirmed_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($order->ready_at)
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></div>
            <span class="text-xs text-black/40 w-20">Prête</span>
            <span class="text-xs font-semibold text-black/70">{{ $order->ready_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($order->delivered_at)
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full bg-gray-400 flex-shrink-0"></div>
            <span class="text-xs text-black/40 w-20">Livrée</span>
            <span class="text-xs font-semibold text-black/70">{{ $order->delivered_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
        @if($order->paid_at)
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full flex-shrink-0" style="background:#C9A84C"></div>
            <span class="text-xs text-black/40 w-20">Payée</span>
            <span class="text-xs font-semibold text-black/70">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Historique client --}}
@php
$clientHistory = \App\Models\LckOrder::where('customer_phone', $order->customer_phone)
    ->where('id', '!=', $order->id)
    ->orderByDesc('created_at')->limit(5)->get();
@endphp
@if($clientHistory->isNotEmpty())
<div class="card mb-3 overflow-hidden">
    <div class="px-4 py-3.5 border-b border-black/5">
        <p class="text-xs font-bold tracking-widest uppercase text-black/30">Autres commandes du client</p>
    </div>
    @foreach($clientHistory as $h)
    <a href="{{ route('commercant.orders.show', $h->order_ref) }}"
       class="flex items-center justify-between px-4 py-3 border-b border-black/5 last:border-0 active:bg-black/2">
        <div>
            <p class="font-mono text-xs font-bold text-black/60">{{ $h->order_ref }}</p>
            <p class="text-xs text-black/30 mt-0.5">{{ $h->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-bold text-black/70">{{ number_format($h->total, 2) }} $</p>
            <p class="text-xs text-black/30 mt-0.5">{{ $h->status_label }}</p>
        </div>
    </a>
    @endforeach
</div>
@endif

{{-- Supprimer --}}
@if(in_array($order->status, ['cancelled', 'delivered']))
<form method="POST" action="{{ route('commercant.orders.destroy', $order->order_ref) }}"
      onsubmit="return confirm('Supprimer définitivement cette commande ?')">
    @csrf
    @method('DELETE')
    <button type="submit"
        class="w-full py-3 rounded-2xl text-xs font-semibold text-black/25 bg-white border border-black/5 active:bg-black/5 transition-colors">
        Supprimer cette commande
    </button>
</form>
@endif

@endsection
