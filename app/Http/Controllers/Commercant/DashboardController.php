<?php

namespace App\Http\Controllers\Commercant;

use App\Http\Controllers\Controller;
use App\Models\LckOrder;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'received'  => LckOrder::where('status', 'received')->count(),
            'preparing' => LckOrder::whereIn('status', ['confirmed', 'preparing'])->count(),
            'ready'     => LckOrder::where('status', 'ready')->count(),
            'today'     => LckOrder::whereDate('created_at', today())->count(),
            'total'     => LckOrder::count(),
            'revenue'   => LckOrder::whereNotIn('status', ['cancelled'])->sum('total'),
        ];

        $recentOrders = LckOrder::with('items')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('commercant.dashboard', compact('stats', 'recentOrders'));
    }
}
