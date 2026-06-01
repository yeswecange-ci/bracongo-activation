@extends('admin.layouts.app')
@section('title', 'LCK — Commandes')
@section('page-title', 'La Clé des Châteaux — Commandes')

@section('content')
<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white rounded-xl shadow-sm p-6">
        <div>
            <h3 class="text-2xl font-bold text-gray-900 flex items-center">
                🍷 Commandes WhatsApp
            </h3>
            <p class="text-sm text-gray-500 mt-1">Toutes les commandes reçues via WhatsApp</p>
        </div>
        <a href="{{ route('admin.lck.orders.index', ['export' => 1]) }}"
           class="mt-4 md:mt-0 inline-flex items-center gap-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter CSV
        </a>
    </div>

    {{-- Filtres + recherche --}}
    <div class="bg-white rounded-xl shadow-sm p-4 flex flex-col md:flex-row gap-3">
        <div class="flex gap-2 flex-wrap">
            @foreach(['all' => 'Toutes', 'received' => 'Reçues', 'confirmed' => 'Confirmées', 'preparing' => 'Préparation', 'ready' => 'Prêtes', 'delivered' => 'Livrées', 'cancelled' => 'Annulées'] as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $key, 'page' => 1]) }}"
               class="px-3 py-1.5 rounded-full text-sm font-medium border transition-colors whitespace-nowrap
                   {{ request('status', 'all') === $key ? 'bg-yellow-600 text-white border-yellow-600' : 'bg-white text-gray-600 border-gray-200 hover:border-yellow-400' }}">
                {{ $label }}
                <span class="opacity-70 text-xs">({{ $stats[$key] ?? 0 }})</span>
            </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('admin.lck.orders.index') }}" class="flex gap-2 md:ml-auto">
            <input type="hidden" name="status" value="{{ request('status', 'all') }}">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Réf, client, téléphone…"
                   class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500">
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">Chercher</button>
        </form>
    </div>

    {{-- Vider les données de test --}}
    <div class="mt-4 md:mt-0 flex items-center gap-2">
        <form method="POST" action="{{ route('admin.lck.orders.destroy-all') }}"
              onsubmit="return confirm('⚠️ Supprimer TOUTES les commandes ?\n\nCette action est irréversible. Utilisez uniquement pour effacer des données de test.') && confirm('Dernière confirmation : effacer toutes les commandes ?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Vider toutes les commandes
            </button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Nouvelles</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['received'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Préparation</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['preparing'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Prêtes</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['ready'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-gray-400">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Livrées</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['delivered'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-red-400">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Annulées</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['cancelled'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-700">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">CA ($)</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['revenue'], 0) }}</p>
        </div>
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
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Référence</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden md:table-cell">Traitée par</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider hidden md:table-cell">Date</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-yellow-50 transition-colors">
                        <td class="px-6 py-4 font-mono font-semibold text-gray-800">{{ $order->order_ref }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900">{{ $order->customer_name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $order->customer_phone }}</p>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800">{{ number_format($order->total, 2) }} $</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                @if($order->status === 'received') bg-blue-100 text-blue-700
                                @elseif($order->status === 'confirmed') bg-indigo-100 text-indigo-700
                                @elseif($order->status === 'preparing') bg-yellow-100 text-yellow-700
                                @elseif($order->status === 'ready') bg-green-100 text-green-700
                                @elseif($order->status === 'delivered') bg-gray-100 text-gray-600
                                @else bg-red-100 text-red-600 @endif">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 hidden md:table-cell">{{ $order->commercant?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 hidden md:table-cell">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('admin.lck.orders.show', $order->order_ref) }}"
                                   class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors inline-flex" title="Voir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.lck.orders.destroy', $order->order_ref) }}"
                                      onsubmit="return confirm('Supprimer {{ $order->order_ref }} ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-400 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
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
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-5xl mb-3">🍷</div>
                            <p class="text-gray-500 font-medium">Aucune commande pour le moment.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
