<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'La Clé des Châteaux') — Espace équipe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .sidebar-bg { background: linear-gradient(180deg, #111111 0%, #1e1209 100%); }
        .nav-active-side { background: rgba(201,168,76,0.15); border-left: 3px solid #c9a84c; color: #c9a84c; }
        body { -webkit-tap-highlight-color: transparent; }
        /* Safe area for iOS bottom notch */
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
    </style>
</head>
<body class="bg-gray-50">

    {{-- ══════════════ DESKTOP SIDEBAR (lg+) ══════════════ --}}
    <aside class="sidebar-bg hidden lg:flex flex-col fixed inset-y-0 left-0 w-64 z-30 shadow-2xl">
        <div class="px-6 py-5 border-b border-white/10">
            <img src="{{ asset('images/lck-logo.jpeg') }}" alt="La Clé des Châteaux" class="h-14 object-contain">
            <p class="text-yellow-500 text-xs mt-2">Espace équipe</p>
        </div>

        <div class="px-5 py-4 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-yellow-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(auth('commercant')->user()->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-white text-sm font-semibold">{{ auth('commercant')->user()->name }}</p>
                    <p class="text-gray-400 text-xs">{{ auth('commercant')->user()->role_label }}</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            @php $pendingCount = \App\Models\LckOrder::whereIn('status',['received','confirmed','preparing'])->count(); @endphp

            <a href="{{ route('commercant.dashboard') }}"
               class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('commercant.dashboard') ? 'nav-active-side' : 'text-gray-400 hover:text-white hover:bg-white/10' }}">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Tableau de bord
            </a>

            <a href="{{ route('commercant.orders.index') }}"
               class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('commercant.orders.*') ? 'nav-active-side' : 'text-gray-400 hover:text-white hover:bg-white/10' }}">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Commandes
                @if($pendingCount > 0)
                    <span class="ml-auto bg-yellow-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                @endif
            </a>

            @if(auth('commercant')->user()->isCaviste())
            <a href="{{ route('commercant.products.index') }}"
               class="flex items-center px-4 py-3 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('commercant.products.*') ? 'nav-active-side' : 'text-gray-400 hover:text-white hover:bg-white/10' }}">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Catalogue
            </a>
            @endif
        </nav>

        <div class="p-4 border-t border-white/10">
            <form method="POST" action="{{ route('commercant.logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-gray-500 hover:text-red-400 transition-colors rounded-xl hover:bg-white/5">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════ MOBILE TOP BAR ══════════════ --}}
    <header class="lg:hidden fixed top-0 inset-x-0 z-30 bg-white border-b border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center">
                <img src="{{ asset('images/lck-logo.jpeg') }}" alt="La Clé des Châteaux" class="h-9 object-contain">
            </div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-yellow-600 flex items-center justify-center text-white font-bold text-xs">
                    {{ strtoupper(substr(auth('commercant')->user()->name, 0, 1)) }}
                </div>
            </div>
        </div>
    </header>

    {{-- ══════════════ MAIN CONTENT ══════════════ --}}
    <main class="lg:ml-64 pt-[60px] lg:pt-0 pb-24 lg:pb-0 min-h-screen">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-4 mt-4 lg:mx-8 lg:mt-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 mt-4 lg:mx-8 lg:mt-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <div class="px-4 py-4 lg:px-8 lg:py-6">
            @yield('content')
        </div>
    </main>

    {{-- ══════════════ MOBILE BOTTOM NAV ══════════════ --}}
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-gray-100 shadow-xl safe-bottom">
        @php $pendingCount = \App\Models\LckOrder::whereIn('status',['received','confirmed','preparing'])->count(); @endphp
        <div class="flex items-stretch h-16">

            {{-- Accueil --}}
            <a href="{{ route('commercant.dashboard') }}"
               class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('commercant.dashboard') ? 'text-yellow-600' : 'text-gray-400' }} active:bg-gray-50 transition-colors">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('commercant.dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ request()->routeIs('commercant.dashboard') ? '0' : '2' }}" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[11px] font-semibold">Accueil</span>
            </a>

            {{-- Commandes --}}
            <a href="{{ route('commercant.orders.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-1 relative {{ request()->routeIs('commercant.orders.*') ? 'text-yellow-600' : 'text-gray-400' }} active:bg-gray-50 transition-colors">
                <div class="relative">
                    <svg class="w-6 h-6" fill="{{ request()->routeIs('commercant.orders.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ request()->routeIs('commercant.orders.*') ? '0' : '2' }}" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span data-pending-badge
                          class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold min-w-[16px] h-4 rounded-full flex items-center justify-center px-1 {{ $pendingCount > 0 ? '' : 'hidden' }}">{{ $pendingCount > 9 ? '9+' : $pendingCount }}</span>
                </div>
                <span class="text-[11px] font-semibold">Commandes</span>
            </a>

            {{-- Catalogue (caviste seulement) --}}
            @if(auth('commercant')->user()->isCaviste())
            <a href="{{ route('commercant.products.index') }}"
               class="flex-1 flex flex-col items-center justify-center gap-1 {{ request()->routeIs('commercant.products.*') ? 'text-yellow-600' : 'text-gray-400' }} active:bg-gray-50 transition-colors">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('commercant.products.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ request()->routeIs('commercant.products.*') ? '0' : '2' }}" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="text-[11px] font-semibold">Catalogue</span>
            </a>
            @endif

            {{-- Déconnexion --}}
            <form method="POST" action="{{ route('commercant.logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full h-full flex flex-col items-center justify-center gap-1 text-gray-400 active:bg-gray-50 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="text-[11px] font-semibold">Quitter</span>
                </button>
            </form>
        </div>
    </nav>

<script>
// Auto-refresh badge nouvelles commandes toutes les 30s
(function () {
    const INTERVAL = 30000;
    const badges   = document.querySelectorAll('[data-pending-badge]');
    if (!badges.length) return;

    async function refreshCount() {
        try {
            const res  = await fetch('{{ route("commercant.pending-count") }}', { credentials: 'same-origin' });
            const data = await res.json();
            const count = data.received ?? 0;
            badges.forEach(el => {
                if (count > 0) {
                    el.textContent = count > 9 ? '9+' : count;
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
        } catch (_) {}
    }

    setInterval(refreshCount, INTERVAL);
})();
</script>
</body>
</html>
