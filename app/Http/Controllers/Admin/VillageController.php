<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Village;
use Illuminate\Http\Request;

class VillageController extends Controller
{
    public function index()
    {
        $villages = Village::withCount(['users', 'partners'])->paginate(10);
        return view('admin.villages.index', compact('villages'));
    }

    public function create()
    {
        return view('admin.villages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Village::create($validated);

        return redirect()->route('admin.villages.index')
            ->with('success', 'Village créé avec succès !');
    }

    public function show(Village $village)
    {
        $village->load(['users', 'partners']);
        return view('admin.villages.show', compact('village'));
    }

    public function edit(Village $village)
    {
        return view('admin.villages.edit', compact('village'));
    }

    public function update(Request $request, Village $village)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $village->update($validated);

        return redirect()->route('admin.villages.index')
            ->with('success', 'Village mis à jour avec succès !');
    }

    public function destroy(Village $village)
    {
        $village->delete();

        return redirect()->route('admin.villages.index')
            ->with('success', 'Village supprimé avec succès !');
    }
}
