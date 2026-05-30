@extends('admin.layouts.app')
@section('title', 'LCK — Rapports')
@section('page-title', 'La Clé des Châteaux — Rapports')

@section('content')
<div class="space-y-6">

    {{-- En-tête + sélecteur période --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white rounded-xl shadow-sm p-6 gap-4">
        <div>
            <h3 class="text-2xl font-bold text-gray-900">📊 Rapports & Analytics</h3>
            <p class="text-sm text-gray-500 mt-1">Depuis le {{ $from->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-2">
            @foreach([7 => '7j', 30 => '30j', 90 => '90j', 365 => '1an'] as $days => $label)
            <a href="{{ request()->fullUrlWithQuery(['period' => $days]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium border transition-colors
                   {{ $period == $days ? 'bg-yellow-600 text-white border-yellow-600' : 'bg-white text-gray-600 border-gray-200 hover:border-yellow-400' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">CA Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($revenue, 0) }} $</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Commandes</p>
            <p class="text-2xl font-bold text-gray-900">{{ $ordersTotal }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $ordersDone }} livrées</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Panier moyen</p>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($avgOrder, 2) }} $</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Taux livraison</p>
            <p class="text-2xl font-bold text-gray-900">
                {{ $ordersTotal > 0 ? number_format($ordersDone / $ordersTotal * 100, 1) : 0 }}%
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Top produits --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">🏆 Top produits vendus</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($topProducts as $i => $product)
                <div class="px-6 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <span class="w-6 h-6 rounded-full {{ $i === 0 ? 'bg-yellow-500' : ($i === 1 ? 'bg-gray-400' : ($i === 2 ? 'bg-amber-600' : 'bg-gray-200')) }} text-white text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $i+1 }}</span>
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $product->product_name }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-bold text-gray-800">{{ number_format($product->revenue, 2) }} $</p>
                        <p class="text-xs text-gray-400">{{ $product->qty_sold }} vendus</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-400 text-sm">Aucune vente sur la période.</div>
                @endforelse
            </div>
        </div>

        {{-- Top zones --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">📍 CA par zone</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @php $maxZoneRevenue = $byZone->max('revenue') ?: 1; @endphp
                @forelse($byZone as $zone)
                <div class="px-6 py-3">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-800">{{ $zone->customer_location }}</p>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800">{{ number_format($zone->revenue, 2) }} $</span>
                            <span class="text-xs text-gray-400 ml-2">{{ $zone->orders_count }} cmd</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-yellow-500 h-1.5 rounded-full" style="width: {{ ($zone->revenue / $maxZoneRevenue * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-400 text-sm">Aucune zone enregistrée.</div>
                @endforelse
            </div>
        </div>

        {{-- CA par commercant --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">👤 Performance par commercant</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nom</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Commandes</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">CA ($)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($byCommercant as $c)
                    <tr class="hover:bg-yellow-50 transition-colors">
                        <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $c->name }}</td>
                        <td class="px-6 py-3 text-center text-sm text-gray-600">{{ $c->orders_count }}</td>
                        <td class="px-6 py-3 text-right text-sm font-bold text-gray-800">{{ number_format($c->revenue ?? 0, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-6 py-6 text-center text-gray-400 text-sm">Aucune donnée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modes de paiement --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                <h3 class="font-bold text-gray-800">💳 Modes de paiement</h3>
            </div>
            <div class="p-6 space-y-4">
                @foreach($paymentMethods as $pm)
                @php $pct = $ordersTotal > 0 ? round($pm->count / $ordersTotal * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700">
                            {{ $pm->payment_method === 'cash_on_delivery' ? '💵 À la livraison' : '📱 Mobile Money' }}
                        </span>
                        <span class="text-sm font-bold text-gray-800">{{ $pct }}% · {{ number_format($pm->revenue, 0) }} $</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="{{ $pm->payment_method === 'cash_on_delivery' ? 'bg-green-500' : 'bg-blue-500' }} h-2 rounded-full"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $pm->count }} commande(s)</p>
                </div>
                @endforeach
            </div>

            {{-- Statuts --}}
            <div class="border-t border-gray-100 px-6 py-4">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Répartition statuts</p>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(['received' => ['Reçues','blue'], 'preparing' => ['Prépa.','yellow'], 'ready' => ['Prêtes','green'], 'delivered' => ['Livrées','gray'], 'cancelled' => ['Annulées','red']] as $s => $info)
                    <div class="text-center">
                        <p class="text-lg font-bold text-gray-800">{{ $statusCounts[$s] ?? 0 }}</p>
                        <p class="text-xs text-gray-400">{{ $info[0] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- CA par jour --}}
    @if($revenueByDay->isNotEmpty())
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <h3 class="font-bold text-gray-800">📈 CA par jour</h3>
        </div>
        <div class="p-6 overflow-x-auto">
            @php $maxDay = $revenueByDay->max('total') ?: 1; @endphp
            <div class="flex items-end gap-1 h-40 min-w-max">
                @foreach($revenueByDay as $day => $data)
                <div class="flex flex-col items-center gap-1" style="min-width: 32px">
                    <span class="text-xs text-gray-500">{{ number_format($data->total, 0) }}</span>
                    <div class="w-6 bg-yellow-500 rounded-t"
                         style="height: {{ max(4, ($data->total / $maxDay) * 120) }}px"
                         title="{{ $day }} : {{ number_format($data->total, 2) }} $"></div>
                    <span class="text-xs text-gray-400" style="writing-mode: vertical-rl; font-size:10px">{{ \Carbon\Carbon::parse($day)->format('d/m') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
