@extends('admin.layouts.app')
@section('title', 'LCK — Nouveau produit')
@section('page-title', 'Nouveau produit')

@section('content')
<div class="space-y-6 max-w-2xl">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm">
        <a href="{{ route('admin.lck.products.index') }}" class="text-yellow-600 hover:text-yellow-800 font-medium flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Catalogue
        </a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">Nouveau produit</span>
    </div>

    @if($errors->any())
    <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-900 px-6 py-4 rounded-lg shadow-sm">
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.lck.products.store') }}"
          class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        @csrf

        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h2 class="font-bold text-gray-800">Informations produit</h2>
        </div>

        <div class="p-6 grid grid-cols-2 gap-5">
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom du produit *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Catégorie</label>
                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent bg-white">
                    <option value="">— Sans catégorie —</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->emoji }} {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Prix ($) *</label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Origine / Région</label>
                <input type="text" name="origin" value="{{ old('origin') }}" placeholder="Bordeaux, France"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Millésime</label>
                <input type="text" name="vintage" value="{{ old('vintage') }}" placeholder="2021"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent resize-none">{{ old('description') }}</textarea>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Label WhatsApp</label>
                <input type="text" name="whatsapp_label" value="{{ old('whatsapp_label') }}" placeholder="Affiché dans le bot (laissez vide = nom du produit)"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock</label>
                <input type="number" name="stock" value="{{ old('stock') }}" min="0" placeholder="Illimité si vide"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Ordre d'affichage</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    ID Produit WordPress
                    <span class="text-xs text-gray-400 font-normal ml-1">— trouvable dans WooCommerce → Produits → colonne ID</span>
                </label>
                <input type="number" name="wordpress_product_id" value="{{ old('wordpress_product_id') }}" min="1"
                       placeholder="Ex: 1042"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
            </div>

            <div class="col-span-2 flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_available" value="1" {{ old('is_available', '1') ? 'checked' : '' }}
                           class="w-4 h-4 accent-yellow-600 rounded">
                    <span class="text-sm text-gray-700">Disponible à la vente</span>
                </label>
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}
                           class="w-4 h-4 accent-yellow-600 rounded">
                    <span class="text-sm text-gray-700">Visible dans le catalogue</span>
                </label>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white px-6 py-2.5 rounded-lg hover:from-yellow-700 hover:to-yellow-800 transition-all shadow-sm text-sm font-medium">
                Créer le produit
            </button>
            <a href="{{ route('admin.lck.products.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Annuler</a>
        </div>
    </form>
</div>
@endsection
