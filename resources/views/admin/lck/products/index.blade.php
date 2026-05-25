@extends('admin.layouts.app')
@section('title', 'LCK — Catalogue')
@section('page-title', 'La Clé des Châteaux — Catalogue')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white rounded-xl shadow-sm p-6">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">🍷 Catalogue produits</h3>
            <p class="text-sm text-gray-500 mt-1">Gérez les vins et spiritueux disponibles</p>
        </div>
        <a href="{{ route('admin.lck.products.create') }}"
           class="mt-4 md:mt-0 inline-flex items-center gap-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white px-5 py-2.5 rounded-lg hover:from-yellow-700 hover:to-yellow-800 transition-all shadow-md text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau produit
        </a>
    </div>

    @if(session('success'))
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 text-green-900 px-6 py-4 rounded-lg shadow-sm flex items-center">
        <svg class="w-5 h-5 mr-3 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Produit</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Dispo</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-yellow-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900">{{ $product->name }}</p>
                            @if($product->vintage)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $product->vintage }} · {{ $product->origin }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right font-bold text-gray-800">{{ number_format($product->price, 2) }} $</td>
                        <td class="px-6 py-4 text-center">
                            <form method="POST" action="{{ route('admin.lck.products.toggle', $product) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors
                                            {{ $product->is_available ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                    {{ $product->is_available ? '✓ Disponible' : '✗ Indispo' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.lck.products.edit', $product) }}"
                                   class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.lck.products.destroy', $product) }}" class="inline"
                                      onsubmit="return confirm('Supprimer ce produit ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-5xl mb-3">🍷</div>
                            <p class="text-gray-500 font-medium">Aucun produit.</p>
                            <a href="{{ route('admin.lck.products.create') }}" class="inline-block mt-3 text-yellow-600 hover:text-yellow-800 font-semibold text-sm">Créer le premier →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">{{ $products->links() }}</div>
        @endif
    </div>
</div>
@endsection
