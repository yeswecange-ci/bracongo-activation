<?php

namespace App\Http\Controllers\Commercant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('commercant')->check()) {
            return redirect()->route('commercant.dashboard');
        }
        return view('commercant.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::guard('commercant')->attempt($credentials, $request->boolean('remember'))) {
            $commercant = Auth::guard('commercant')->user();

            if (!$commercant->is_active) {
                Auth::guard('commercant')->logout();
                return back()->withErrors([
                    'email' => 'Votre compte a été désactivé. Contactez un administrateur.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('commercant.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email ou mot de passe incorrect.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('commercant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('commercant.login');
    }
}
