<?php

namespace App\Http\Controllers;

use App\Models\LckOrder;
use App\Models\LckSetting;

class LckReceiptController extends Controller
{
    public function show(string $ref)
    {
        // Token de sécurité pour éviter l'énumération des commandes
        $expected = substr(hash('sha256', $ref . config('app.key')), 0, 16);
        $provided = request('t');

        if ($provided !== $expected) {
            abort(404);
        }

        $order    = LckOrder::with('items')->where('order_ref', $ref)->firstOrFail();
        $settings = LckSetting::asMap();

        return view('lck.receipt', compact('order', 'settings'));
    }
}
