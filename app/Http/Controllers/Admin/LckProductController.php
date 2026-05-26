<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LckCategory;
use App\Models\LckProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LckProductController extends Controller
{
    public function index()
    {
        $products   = LckProduct::with('category')->orderBy('sort_order')->paginate(20);
        $categories = LckCategory::active()->get();
        return view('admin.lck.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = LckCategory::active()->get();
        return view('admin.lck.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'           => 'nullable|exists:lck_categories,id',
            'name'                  => 'required|string|max:200',
            'description'           => 'nullable|string',
            'origin'                => 'nullable|string|max:100',
            'vintage'               => 'nullable|string|max:10',
            'price'                 => 'required|numeric|min:0',
            'whatsapp_label'        => 'nullable|string|max:150',
            'stock'                 => 'nullable|integer|min:0',
            'is_available'          => 'boolean',
            'is_active'             => 'boolean',
            'sort_order'            => 'nullable|integer',
            'wordpress_product_id'  => 'nullable|integer|unique:lck_products,wordpress_product_id',
        ]);

        $data['slug']                   = Str::slug($data['name'] . '-' . ($data['vintage'] ?? ''));
        $data['whatsapp_label']         = ($data['whatsapp_label'] ?? null) ?: $data['name'];
        $data['is_available']           = $request->boolean('is_available', true);
        $data['is_active']              = $request->boolean('is_active', true);
        $data['sort_order']             = $data['sort_order'] ?? 0;
        $data['wordpress_product_id']   = $data['wordpress_product_id'] ?? null;

        LckProduct::create($data);

        return redirect()->route('admin.lck.products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function edit(LckProduct $product)
    {
        $categories = LckCategory::active()->get();
        return view('admin.lck.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, LckProduct $product)
    {
        $data = $request->validate([
            'category_id'           => 'nullable|exists:lck_categories,id',
            'name'                  => 'required|string|max:200',
            'description'           => 'nullable|string',
            'origin'                => 'nullable|string|max:100',
            'vintage'               => 'nullable|string|max:10',
            'price'                 => 'required|numeric|min:0',
            'whatsapp_label'        => 'nullable|string|max:150',
            'stock'                 => 'nullable|integer|min:0',
            'is_available'          => 'boolean',
            'is_active'             => 'boolean',
            'sort_order'            => 'nullable|integer',
            'wordpress_product_id'  => 'nullable|integer|unique:lck_products,wordpress_product_id,' . $product->id,
        ]);

        $data['whatsapp_label']        = ($data['whatsapp_label'] ?? null) ?: $data['name'];
        $data['is_available']          = $request->boolean('is_available');
        $data['is_active']             = $request->boolean('is_active');
        $data['sort_order']            = $data['sort_order'] ?? 0;
        $data['wordpress_product_id']  = $data['wordpress_product_id'] ?? null;
        $product->update($data);

        return redirect()->route('admin.lck.products.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(LckProduct $product)
    {
        $product->delete();
        return redirect()->route('admin.lck.products.index')
            ->with('success', 'Produit supprimé.');
    }

    public function toggleAvailability(LckProduct $product)
    {
        $product->update(['is_available' => !$product->is_available]);
        return back()->with('success', "Disponibilité mise à jour.");
    }
}
