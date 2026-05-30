<?php

namespace App\Http\Controllers\Commercant;

use App\Http\Controllers\Controller;
use App\Models\LckOrder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $myId = Auth::guard('commercant')->id();

        // Stats globales (toutes les commandes)
        $stats = [
            'received'  => LckOrder::where('status', 'received')->count(),
            'preparing' => LckOrder::whereIn('status', ['confirmed', 'preparing'])->count(),
            'ready'     => LckOrder::where('status', 'ready')->count(),
            'total'     => LckOrder::count(),
            'revenue'   => LckOrder::whereNotIn('status', ['cancelled'])->sum('total'),
            'unclaimed' => LckOrder::whereNull('commercant_id')->whereNotIn('status', ['delivered','cancelled'])->count(),
        ];

        // Stats personnelles (commandes du commercant connecté)
        $myOrders = LckOrder::where('commercant_id', $myId);
        $myStats  = [
            'total'     => (clone $myOrders)->count(),
            'delivered' => (clone $myOrders)->where('status', 'delivered')->count(),
            'today'     => (clone $myOrders)->whereDate('created_at', today())->count(),
            'revenue'   => (clone $myOrders)->where('status', 'delivered')->sum('total'),
        ];

        $recentOrders = LckOrder::with('items')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('commercant.dashboard', compact('stats', 'myStats', 'recentOrders'));
    }
}
