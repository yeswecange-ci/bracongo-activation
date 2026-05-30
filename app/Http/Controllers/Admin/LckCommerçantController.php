<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commercant;
use Illuminate\Http\Request;

class LckCommerçantController extends Controller
{
    public function index()
    {
        $commercants = Commercant::orderBy('name')->get();
        return view('admin.lck.commercants.index', compact('commercants'));
    }

    public function create()
    {
        return view('admin.lck.commercants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:commercants,email',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:commercial,caviste',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['zones']     = $this->parseZones($request->input('zones', ''));

        Commercant::create($data);

        return redirect()->route('admin.lck.commercants.index')
            ->with('success', "Compte créé pour {$data['name']}.");
    }

    public function edit(Commercant $commercant)
    {
        return view('admin.lck.commercants.edit', compact('commercant'));
    }

    public function update(Request $request, Commercant $commercant)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => "required|email|unique:commercants,email,{$commercant->id}",
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|in:commercial,caviste',
            'is_active' => 'boolean',
            'password'  => 'nullable|string|min:8|confirmed',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['zones']     = $this->parseZones($request->input('zones', ''));

        if (!$request->filled('password')) {
            unset($data['password']);
        }

        $commercant->update($data);

        return redirect()->route('admin.lck.commercants.index')
            ->with('success', "Compte mis à jour.");
    }

    public function destroy(Commercant $commercant)
    {
        $commercant->delete();
        return redirect()->route('admin.lck.commercants.index')
            ->with('success', "Compte supprimé.");
    }

    private function parseZones(string $raw): array
    {
        return collect(explode("\n", $raw))
            ->map(fn($z) => trim($z))
            ->filter(fn($z) => $z !== '')
            ->values()
            ->all();
    }

    public function toggleActive(Commercant $commercant)
    {
        $commercant->update(['is_active' => !$commercant->is_active]);
        $label = $commercant->fresh()->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Compte {$label}.");
    }
}
