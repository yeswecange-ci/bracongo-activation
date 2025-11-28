<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\Partner;
use Illuminate\Http\Request;

class PrizeController extends Controller
{
    public function index()
    {
        $prizes = Prize::with('partner')->paginate(10);
        return view('admin.prizes.index', compact('prizes'));
    }

    public function create()
    {
        $partners = Partner::where('is_active', true)->get();
        return view('admin.prizes.create', compact('partners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'partner_id' => 'nullable|exists:partners,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['distributed_count'] = 0;

        Prize::create($validated);

        return redirect()->route('admin.prizes.index')
            ->with('success', 'Lot créé avec succès !');
    }

    public function show(Prize $prize)
    {
        $prize->load(['partner', 'winners']);
        return view('admin.prizes.show', compact('prize'));
    }

    public function edit(Prize $prize)
    {
        $partners = Partner::where('is_active', true)->get();
        return view('admin.prizes.edit', compact('prize', 'partners'));
    }

    public function update(Request $request, Prize $prize)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'partner_id' => 'nullable|exists:partners,id',
            'quantity' => 'required|integer|min:1',
            'distributed_count' => 'required|integer|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $prize->update($validated);

        return redirect()->route('admin.prizes.index')
            ->with('success', 'Lot mis à jour avec succès !');
    }

    public function destroy(Prize $prize)
    {
        $prize->delete();

        return redirect()->route('admin.prizes.index')
            ->with('success', 'Lot supprimé avec succès !');
    }
}
