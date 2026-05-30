<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Espace équipe') — LCK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { background: #F8F6F2; -webkit-tap-highlight-color: transparent; }

        /* Sidebar desktop */
        .sidebar { background: #111; }
        .nav-active { background: rgba(201,168,76,0.12); color: #C9A84C; }
        .nav-active-dot::after {
            content: ''; display: block; width: 3px; height: 28px;
            background: #C9A84C; border-radius: 0 3px 3px 0;
            position: absolute; left: 0; top: 50%; transform: translateY(-50%);
        }

        /* Bottom nav */
        .bottom-nav { background: #111; }
        .bottom-nav-item { color: #6b6b6b; }
        .bottom-nav-item.active { color: #C9A84C; }

        /* Cards */
        .card { background: #fff; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06); }
        .card-sm { background: #fff; border-radius: 12px; border: 1px solid rgba(0,0,0,0.06); }

        /* iOS safe area */
        .safe-pb { padding-bottom: env(safe-area-inset-bottom, 0px); }

        /* Scrollbar hidden */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { scrollbar-width: none; }

        /* Smooth transitions */
        * { -webkit-font-smoothing: antialiased; }
    </style>
</head>
<body>

{{-- ══════════════ DESKTOP SIDEBAR (lg+) ══════════════ --}}
<aside class="sidebar hidden lg:flex flex-col fixed inset-y-0 left-0 w-64 z-30">

    {{-- Logo --}}
    <div class="px-6 py-6 border-b border-white/5">
        <img src="{{ asset('images/lck-logo.jpeg') }}" alt="La Clé des Châteaux" class="h-10 object-contain">
    </div>

    {{-- User --}}
    <div class="px-4 py-4 border-b border-white/5">
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-white/5">
            <div class="w-8 h-8 rounded-full bg-[#C9A84C] flex items-center justify-center text-black font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr(auth('commercant')->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-white text-sm font-semibold truncate">{{ auth('commercant')->user()->name }}</p>
                <p class="text-white/40 text-xs">{{ auth('commercant')->user()->role_label }}</p>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        @php $pending = \App\Models\LckOrder::whereIn('status',['received','confirmed','preparing'])->count(); @endphp

        <a href="{{ route('commercant.dashboard') }}"
           class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
               {{ request()->routeIs('commercant.dashboard') ? 'nav-active nav-active-dot' : 'text-white/50 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="{{ request()->routeIs('commercant.dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Accueil
        </a>

        <a href="{{ route('commercant.orders.index') }}"
           class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
               {{ request()->routeIs('commercant.orders.*') ? 'nav-active nav-active-dot' : 'text-white/50 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="{{ request()->routeIs('commercant.orders.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Commandes
            @if($pending > 0)
            <span class="ml-auto bg-[#C9A84C] text-black text-xs font-bold px-2 py-0.5 rounded-full">{{ $pending }}</span>
            @endif
        </a>

        @if(auth('commercant')->user()->isCaviste())
        <a href="{{ route('commercant.products.index') }}"
           class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
               {{ request()->routeIs('commercant.products.*') ? 'nav-active nav-active-dot' : 'text-white/50 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="{{ request()->routeIs('commercant.products.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Catalogue
        </a>
        @endif
    </nav>

    {{-- Logout --}}
    <div class="px-4 pb-6 border-t border-white/5 pt-4">
        <form method="POST" action="{{ route('commercant.logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 text-sm text-white/30 hover:text-red-400 transition-colors rounded-xl hover:bg-white/5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Déconnexion
            </button>
        </form>
    </div>
</aside>

{{-- ══════════════ MOBILE TOP BAR ══════════════ --}}
<header class="lg:hidden fixed top-0 inset-x-0 z-30 bg-white/90 backdrop-blur-md border-b border-black/5">
    <div class="flex items-center justify-between px-5 h-14">
        <img src="{{ asset('images/lck-logo.jpeg') }}" alt="LCK" class="h-8 object-contain">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#C9A84C] flex items-center justify-center text-black font-bold text-xs">
                {{ strtoupper(substr(auth('commercant')->user()->name, 0, 1)) }}
            </div>
        </div>
    </div>
</header>

{{-- ══════════════ MAIN CONTENT ══════════════ --}}
<main class="lg:ml-64 pt-14 lg:pt-0 pb-24 lg:pb-0 min-h-screen">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mx-4 mt-4 lg:mx-8 lg:mt-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mx-4 mt-4 lg:mx-8 lg:mt-6 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <div class="px-4 py-5 lg:px-8 lg:py-7">
        @yield('content')
    </div>
</main>

{{-- ══════════════ MOBILE BOTTOM NAV ══════════════ --}}
<nav class="bottom-nav lg:hidden fixed bottom-0 inset-x-0 z-30 safe-pb">
    @php $pendingCount = \App\Models\LckOrder::whereIn('status',['received','confirmed','preparing'])->count(); @endphp
    <div class="flex items-stretch h-16 px-2">

        {{-- Accueil --}}
        <a href="{{ route('commercant.dashboard') }}"
           class="bottom-nav-item flex-1 flex flex-col items-center justify-center gap-1 rounded-xl mx-0.5 transition-colors {{ request()->routeIs('commercant.dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('commercant.dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-semibold tracking-wide">Accueil</span>
        </a>

        {{-- Commandes --}}
        <a href="{{ route('commercant.orders.index') }}"
           class="bottom-nav-item flex-1 flex flex-col items-center justify-center gap-1 rounded-xl mx-0.5 relative transition-colors {{ request()->routeIs('commercant.orders.*') ? 'active' : '' }}">
            <div class="relative">
                <svg class="w-5 h-5" fill="{{ request()->routeIs('commercant.orders.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span data-pending-badge
                      class="absolute -top-1 -right-2 bg-[#C9A84C] text-black text-[9px] font-black min-w-[15px] h-[15px] rounded-full flex items-center justify-center px-1 {{ $pendingCount > 0 ? '' : 'hidden' }}">{{ $pendingCount > 9 ? '9+' : $pendingCount }}</span>
            </div>
            <span class="text-[10px] font-semibold tracking-wide">Commandes</span>
        </a>

        {{-- Catalogue (caviste) --}}
        @if(auth('commercant')->user()->isCaviste())
        <a href="{{ route('commercant.products.index') }}"
           class="bottom-nav-item flex-1 flex flex-col items-center justify-center gap-1 rounded-xl mx-0.5 transition-colors {{ request()->routeIs('commercant.products.*') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('commercant.products.*') ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="text-[10px] font-semibold tracking-wide">Catalogue</span>
        </a>
        @endif

        {{-- Déconnexion --}}
        <form method="POST" action="{{ route('commercant.logout') }}" class="flex-1 mx-0.5">
            @csrf
            <button type="submit" class="bottom-nav-item w-full h-full flex flex-col items-center justify-center gap-1 rounded-xl transition-colors hover:text-red-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span class="text-[10px] font-semibold tracking-wide">Sortir</span>
            </button>
        </form>
    </div>
</nav>

<script>
(function () {
    const INTERVAL = 30000;
    const badges = document.querySelectorAll('[data-pending-badge]');
    if (!badges.length) return;
    async function refresh() {
        try {
            const res  = await fetch('{{ route("commercant.pending-count") }}', { credentials: 'same-origin' });
            const data = await res.json();
            const n = data.received ?? 0;
            badges.forEach(el => {
                el.textContent = n > 9 ? '9+' : n;
                el.classList.toggle('hidden', n === 0);
            });
        } catch (_) {}
    }
    setInterval(refresh, INTERVAL);
})();
</script>

</body>
</html>
