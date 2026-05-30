<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Connexion — La Clé des Châteaux</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        * { font-family: 'Inter', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        body {
            background: #0D0D0D;
            background-image:
                radial-gradient(ellipse 80% 60% at 50% 0%, rgba(201,168,76,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 60% 40% at 80% 100%, rgba(201,168,76,0.05) 0%, transparent 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
    </style>
</head>
<body>

<div class="w-full max-w-[360px]">

    {{-- Logo --}}
    <div class="text-center mb-10">
        <img src="{{ asset('images/lck-logo.jpeg') }}" alt="La Clé des Châteaux"
             class="h-16 mx-auto mb-5 object-contain">
        <p class="text-xs font-semibold tracking-[0.2em] uppercase text-white/30">Espace équipe</p>
    </div>

    {{-- Card --}}
    <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; backdrop-filter: blur(20px); padding: 32px;">

        @if ($errors->any())
        <div class="flex items-center gap-3 mb-6 px-4 py-3 rounded-xl" style="background: rgba(220,38,38,0.1); border: 1px solid rgba(220,38,38,0.2)">
            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-red-400 text-sm">{{ $errors->first() }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('commercant.login.post') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold tracking-wide text-white/40 uppercase mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       placeholder="votre@email.com"
                       style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 12px; padding: 14px 16px; font-size: 15px; outline: none; transition: border-color 0.2s;"
                       onfocus="this.style.borderColor='rgba(201,168,76,0.5)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
            </div>

            <div>
                <label class="block text-xs font-semibold tracking-wide text-white/40 uppercase mb-2">Mot de passe</label>
                <input type="password" name="password" required
                       placeholder="••••••••"
                       style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 12px; padding: 14px 16px; font-size: 15px; outline: none; transition: border-color 0.2s;"
                       onfocus="this.style.borderColor='rgba(201,168,76,0.5)'"
                       onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
            </div>

            <div class="flex items-center gap-2.5 pt-1">
                <input type="checkbox" name="remember" id="remember"
                       style="width: 16px; height: 16px; accent-color: #C9A84C; border-radius: 4px; cursor: pointer;">
                <label for="remember" class="text-sm text-white/40 cursor-pointer">Se souvenir de moi</label>
            </div>

            <button type="submit"
                    style="width: 100%; background: #C9A84C; color: #000; font-weight: 700; font-size: 15px; padding: 15px; border-radius: 12px; border: none; cursor: pointer; letter-spacing: 0.02em; margin-top: 8px; transition: opacity 0.2s;"
                    onmouseover="this.style.opacity='0.9'"
                    onmouseout="this.style.opacity='1'">
                Se connecter
            </button>
        </form>
    </div>

    <p class="text-center text-white/15 text-xs mt-8">
        La Clé des Châteaux · Bracongo RDC
    </p>
</div>

</body>
</html>
