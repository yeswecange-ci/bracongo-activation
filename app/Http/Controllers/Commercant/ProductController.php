<?php

namespace App\Http\Controllers\Commercant;

use App\Http\Controllers\Controller;
use App\Models\LckProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        // Seuls les cavistes peuvent accéder au catalogue
        if (!Auth::guard('commercant')->user()->isCaviste()) {
            abort(403, 'Accès réservé aux cavistes.');
        }

        $products = LckProduct::with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($p) => $p->category?->name ?? 'Sans catégorie');

        return view('commercant.products.index', compact('products'));
    }

    public function toggle(Request $request, int $id)
    {
        if (!Auth::guard('commercant')->user()->isCaviste()) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $product = LckProduct::findOrFail($id);
        $product->update(['is_available' => !$product->is_available]);

        return response()->json([
            'success'      => true,
            'is_available' => $product->is_available,
            'label'        => $product->is_available ? 'Disponible' : 'Indisponible',
        ]);
    }

    public function updateStock(Request $request, int $id)
    {
        if (!Auth::guard('commercant')->user()->isCaviste()) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $request->validate(['stock' => 'required|integer|min:0']);

        $product = LckProduct::findOrFail($id);
        $product->update([
            'stock'        => $request->stock,
            'is_available' => $request->stock > 0,
        ]);

        return response()->json([
            'success' => true,
            'stock'   => $product->stock,
            'is_available' => $product->is_available,
        ]);
    }
}
