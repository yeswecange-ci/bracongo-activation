@extends('commercant.layouts.app')
@section('title', 'Catalogue')

@section('content')

<p class="text-sm text-gray-500 mb-4">
    Gérez la disponibilité et le stock. Les modifications sont appliquées immédiatement sur le site et le bot.
</p>

{{-- Toast notification --}}
<div id="toast" class="hidden fixed top-20 left-4 right-4 lg:left-auto lg:right-6 lg:w-80 z-50 px-5 py-4 rounded-2xl shadow-xl text-sm font-semibold text-center transition-all"></div>

@foreach($products as $categoryName => $items)
<div class="bg-white rounded-2xl shadow-sm mb-4 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div>
            <h2 class="font-bold text-gray-800">{{ $categoryName }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ $items->count() }} produit(s)</p>
        </div>
    </div>

    <div class="divide-y divide-gray-50">
        @foreach($items as $product)
        @php
            $isLowStock = $product->stock !== null && $product->stock <= $product->stock_alert_threshold && $product->stock > 0;
            $isOutOfStock = $product->stock !== null && $product->stock <= 0;
        @endphp
        <div class="px-5 py-4" id="product-row-{{ $product->id }}">
            <div class="flex items-center justify-between gap-4 mb-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-semibold text-gray-800 text-sm">{{ $product->name }}</p>
                        @if($isOutOfStock)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-600">Rupture</span>
                        @elseif($isLowStock)
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-600">⚠️ Stock bas</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <span class="text-sm font-bold text-gray-700">{{ number_format($product->price, 2) }} $</span>
                        @if($product->stock !== null)
                        <span id="stock-label-{{ $product->id }}" class="text-xs {{ $isOutOfStock ? 'text-red-500 font-bold' : ($isLowStock ? 'text-orange-500 font-semibold' : 'text-gray-400') }}">
                            · {{ $product->stock }} en stock
                        </span>
                        @else
                        <span class="text-xs text-gray-400">· Stock illimité</span>
                        @endif
                    </div>
                    <p id="label-{{ $product->id }}"
                       class="text-xs font-semibold mt-1 {{ $product->is_available ? 'text-green-600' : 'text-gray-400' }}">
                        {{ $product->is_available ? '✓ Disponible' : '✗ Indisponible' }}
                    </p>
                </div>

                {{-- Toggle switch --}}
                <button onclick="toggleProduct({{ $product->id }})"
                        id="toggle-{{ $product->id }}"
                        class="relative flex-shrink-0 h-8 w-14 rounded-full transition-colors duration-200 focus:outline-none
                            {{ $product->is_available ? 'bg-green-500' : 'bg-gray-200' }}"
                        role="switch" aria-checked="{{ $product->is_available ? 'true' : 'false' }}">
                    <span id="dot-{{ $product->id }}"
                          class="absolute top-1 h-6 w-6 rounded-full bg-white shadow-md transform transition-transform duration-200
                              {{ $product->is_available ? 'translate-x-7' : 'translate-x-1' }}">
                    </span>
                </button>
            </div>

            {{-- Mise à jour stock --}}
            @if($product->stock !== null)
            <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2">
                <span class="text-xs text-gray-500 font-medium flex-shrink-0">Stock :</span>
                <button onclick="adjustStock({{ $product->id }}, -1)"
                        class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-600 font-bold text-lg flex items-center justify-center active:bg-gray-100 transition-colors flex-shrink-0">−</button>
                <span id="stock-count-{{ $product->id }}" class="flex-1 text-center font-bold text-gray-800 text-sm">{{ $product->stock }}</span>
                <button onclick="adjustStock({{ $product->id }}, +1)"
                        class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-600 font-bold text-lg flex items-center justify-center active:bg-gray-100 transition-colors flex-shrink-0">+</button>
                <button onclick="saveStock({{ $product->id }})"
                        id="save-{{ $product->id }}"
                        class="hidden px-3 py-1.5 rounded-lg bg-yellow-600 text-white text-xs font-bold active:bg-yellow-700 transition-colors flex-shrink-0">
                    Sauver
                </button>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endforeach

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const stockChanges = {};

async function toggleProduct(id) {
    const btn   = document.getElementById('toggle-' + id);
    const dot   = document.getElementById('dot-' + id);
    const label = document.getElementById('label-' + id);

    btn.disabled = true;
    btn.style.opacity = '0.6';

    try {
        const res  = await fetch(`/commercant/products/${id}/toggle`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.success) {
            if (data.is_available) {
                btn.classList.replace('bg-gray-200', 'bg-green-500');
                dot.classList.replace('translate-x-1', 'translate-x-7');
                label.className = 'text-xs font-semibold mt-1 text-green-600';
                label.textContent = '✓ Disponible';
            } else {
                btn.classList.replace('bg-green-500', 'bg-gray-200');
                dot.classList.replace('translate-x-7', 'translate-x-1');
                label.className = 'text-xs font-semibold mt-1 text-gray-400';
                label.textContent = '✗ Indisponible';
            }
            showToast(data.is_available ? '✅ Disponible' : '⚠️ Indisponible', false);
        }
    } catch (e) {
        showToast('❌ Erreur de connexion', true);
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
    }
}

function adjustStock(id, delta) {
    const el  = document.getElementById('stock-count-' + id);
    const btn = document.getElementById('save-' + id);
    const current = parseInt(el.textContent) || 0;
    const newVal  = Math.max(0, current + delta);
    el.textContent = newVal;
    stockChanges[id] = newVal;
    if (btn) btn.classList.remove('hidden');
}

async function saveStock(id) {
    const btn  = document.getElementById('save-' + id);
    const qty  = stockChanges[id];
    btn.textContent = '…';
    btn.disabled = true;

    try {
        const res  = await fetch(`/commercant/products/${id}/stock`, {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body:    JSON.stringify({ stock: qty }),
        });
        const data = await res.json();

        if (data.success) {
            const lbl = document.getElementById('stock-label-' + id);
            if (lbl) lbl.textContent = '· ' + qty + ' en stock';
            showToast('✅ Stock mis à jour', false);
            btn.classList.add('hidden');
        }
    } catch (e) {
        showToast('❌ Erreur', true);
    } finally {
        btn.textContent = 'Sauver';
        btn.disabled = false;
    }
}

function showToast(message, error = false) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `fixed top-20 left-4 right-4 lg:left-auto lg:right-6 lg:w-80 z-50 px-5 py-4 rounded-2xl shadow-xl text-sm font-semibold text-center ${error ? 'bg-red-500' : 'bg-green-600'} text-white`;
    setTimeout(() => { toast.className = 'hidden'; }, 2500);
}
</script>

@endsection
