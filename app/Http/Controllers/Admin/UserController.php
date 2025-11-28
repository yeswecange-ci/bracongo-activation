<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('village');

        // Filtres
        if ($request->has('village_id') && $request->village_id != '') {
            $query->where('village_id', $request->village_id);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $villages = Village::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'villages'));
    }

    public function show(User $user)
    {
        $user->load(['village', 'pronostics.match', 'prizes']);
        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Joueur supprimé avec succès !');
    }
}
