<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CommerçantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('commercant')->check()) {
            return redirect()->route('commercant.login');
        }

        if (!Auth::guard('commercant')->user()->is_active) {
            Auth::guard('commercant')->logout();
            return redirect()->route('commercant.login')
                ->withErrors(['email' => 'Votre compte a été désactivé. Contactez un administrateur.']);
        }

        return $next($request);
    }
}
