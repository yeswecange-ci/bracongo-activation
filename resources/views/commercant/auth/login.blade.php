<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>La Clé des Châteaux — Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(145deg, #0f0f0f 0%, #1e1108 60%, #0f0f0f 100%); }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center px-5 py-10">

    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="text-center mb-10">
            <img src="{{ asset('images/lck-logo.jpeg') }}" alt="La Clé des Châteaux" class="h-24 mx-auto mb-4 object-contain">
            <p class="text-yellow-500 text-sm font-semibold tracking-widest uppercase">Espace équipe</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <div class="bg-gray-900 px-6 py-5">
                <h2 class="text-white text-lg font-bold">Connexion</h2>
                <p class="text-gray-400 text-sm mt-0.5">Accès réservé à l'équipe</p>
            </div>

            @if ($errors->any())
            <div class="mx-5 mt-5 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('commercant.login.post') }}" class="p-5 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-2xl px-4 py-4 text-base focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all"
                           placeholder="votre@email.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe</label>
                    <input type="password" name="password" required
                           class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-2xl px-4 py-4 text-base focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all"
                           placeholder="••••••••">
                </div>

                <label class="flex items-center gap-3 cursor-pointer py-1">
                    <input type="checkbox" name="remember" class="w-5 h-5 accent-yellow-600 rounded">
                    <span class="text-sm text-gray-600 font-medium">Se souvenir de moi</span>
                </label>

                <button type="submit"
                        class="w-full bg-yellow-600 hover:bg-yellow-700 active:bg-yellow-800 text-white font-bold py-4 rounded-2xl text-base tracking-wide transition-colors shadow-lg mt-2">
                    Se connecter →
                </button>
            </form>
        </div>

        <p class="text-center text-gray-600 text-xs mt-8">
            Bracongo RDC — La Clé des Châteaux &copy; {{ date('Y') }}
        </p>
    </div>

</body>
</html>
