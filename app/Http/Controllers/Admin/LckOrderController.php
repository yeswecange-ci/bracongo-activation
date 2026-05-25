<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LckOrder;

class LckOrderController extends Controller
{
    public function index()
    {
        $orders = LckOrder::with(['items', 'commercant'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = [
            'received'  => LckOrder::where('status', 'received')->count(),
            'preparing' => LckOrder::whereIn('status', ['confirmed', 'preparing'])->count(),
            'ready'     => LckOrder::where('status', 'ready')->count(),
            'delivered' => LckOrder::where('status', 'delivered')->count(),
            'cancelled' => LckOrder::where('status', 'cancelled')->count(),
            'revenue'   => LckOrder::whereNotIn('status', ['cancelled'])->sum('total'),
        ];

        return view('admin.lck.orders.index', compact('orders', 'stats'));
    }

    public function show(string $ref)
    {
        $order = LckOrder::with(['items.product', 'commercant'])
            ->where('order_ref', $ref)
            ->firstOrFail();

        return view('admin.lck.orders.show', compact('order'));
    }
}
