@extends('commercant.layouts.app')
@section('title', 'Catalogue')

@section('content')

<p class="text-sm text-gray-500 mb-5">
    Activez ou désactivez la disponibilité. Le site et le bot WhatsApp se mettent à jour automatiquement.
</p>

{{-- Toast notification --}}
<div id="toast" class="hidden fixed top-20 left-4 right-4 lg:left-auto lg:right-6 lg:w-80 z-50 px-5 py-4 rounded-2xl shadow-xl text-sm font-semibold text-center transition-all"></div>

@foreach($products as $categoryName => $items)
<div class="bg-white rounded-2xl shadow-sm mb-4 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
        <h2 class="font-bold text-gray-800">{{ $categoryName }}</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $items->count() }} produit(s)</p>
    </div>

    <div class="divide-y divide-gray-50">
        @foreach($items as $product)
        <div class="px-5 py-4 flex items-center justify-between gap-4" id="product-row-{{ $product->id }}">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-800 text-sm">{{ $product->name }}</p>
                <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                    <span class="text-sm font-bold text-gray-700">{{ number_format($product->price, 2) }} $</span>
                    @if($product->vintage)
                        <span class="text-xs text-gray-400">· {{ $product->vintage }}</span>
                    @endif
                    @if($product->origin)
                        <span class="text-xs text-gray-400">· {{ $product->origin }}</span>
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
        @endforeach
    </div>
</div>
@endforeach

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

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
            showToast(data.is_available ? '✅ Marqué comme disponible' : '⚠️ Marqué comme indisponible', false);
        }
    } catch (e) {
        showToast('❌ Erreur de connexion', true);
    } finally {
        btn.disabled = false;
        btn.style.opacity = '1';
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
