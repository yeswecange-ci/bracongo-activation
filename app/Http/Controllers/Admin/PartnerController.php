<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::with('village')->paginate(10);
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        $villages = Village::where('is_active', true)->get();
        return view('admin.partners.create', compact('villages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Upload du logo
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('partners/logos', 'public');
        }

        Partner::create($validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partenaire créé avec succès !');
    }

    public function show(Partner $partner)
    {
        $partner->load('village', 'prizes');
        return view('admin.partners.show', compact('partner'));
    }

    public function edit(Partner $partner)
    {
        $villages = Village::where('is_active', true)->get();
        return view('admin.partners.edit', compact('partner', 'villages'));
    }

    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Upload du nouveau logo
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($partner->logo) {
                Storage::disk('public')->delete($partner->logo);
            }
            $validated['logo'] = $request->file('logo')->store('partners/logos', 'public');
        }

        $partner->update($validated);

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partenaire mis à jour avec succès !');
    }

    public function destroy(Partner $partner)
    {
        // Supprimer le logo
        if ($partner->logo) {
            Storage::disk('public')->delete($partner->logo);
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partenaire supprimé avec succès !');
    }
}
