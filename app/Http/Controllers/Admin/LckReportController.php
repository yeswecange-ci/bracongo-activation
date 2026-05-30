<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commercant;
use App\Models\LckOrder;
use App\Models\LckProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LckReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // jours

        $from = now()->subDays((int) $period)->startOfDay();

        // ── CA global ────────────────────────────────────────────
        $revenue = LckOrder::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $from)->sum('total');

        $ordersTotal = LckOrder::where('created_at', '>=', $from)->count();
        $ordersDone  = LckOrder::where('status', 'delivered')
            ->where('created_at', '>=', $from)->count();
        $avgOrder    = $ordersTotal > 0
            ? LckOrder::whereNotIn('status', ['cancelled'])->where('created_at', '>=', $from)->avg('total')
            : 0;

        // ── CA par jour (courbe) ──────────────────────────────────
        $revenueByDay = LckOrder::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $from)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // ── CA par zone ───────────────────────────────────────────
        $byZone = LckOrder::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $from)
            ->whereNotNull('customer_location')
            ->select('customer_location', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('customer_location')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // ── CA par commercant ─────────────────────────────────────
        $byCommercant = Commercant::withCount([
                'orders as orders_count' => fn($q) => $q->whereNotIn('status', ['cancelled'])->where('created_at', '>=', $from),
            ])
            ->withSum([
                'orders as revenue' => fn($q) => $q->whereNotIn('status', ['cancelled'])->where('created_at', '>=', $from),
            ], 'total')
            ->having('orders_count', '>', 0)
            ->orderByDesc('revenue')
            ->get();

        // ── Top produits vendus ───────────────────────────────────
        $topProducts = DB::table('lck_order_items')
            ->join('lck_orders', 'lck_orders.id', '=', 'lck_order_items.order_id')
            ->whereNotIn('lck_orders.status', ['cancelled'])
            ->where('lck_orders.created_at', '>=', $from)
            ->select(
                'lck_order_items.product_name',
                DB::raw('SUM(lck_order_items.quantity) as qty_sold'),
                DB::raw('SUM(lck_order_items.subtotal) as revenue')
            )
            ->groupBy('lck_order_items.product_name')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get();

        // ── Répartition statuts ───────────────────────────────────
        $statusCounts = LckOrder::where('created_at', '>=', $from)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Répartition modes de paiement ────────────────────────
        $paymentMethods = LckOrder::whereNotIn('status', ['cancelled'])
            ->where('created_at', '>=', $from)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as revenue'))
            ->groupBy('payment_method')
            ->get();

        return view('admin.lck.reports.index', compact(
            'period', 'from', 'revenue', 'ordersTotal', 'ordersDone',
            'avgOrder', 'revenueByDay', 'byZone', 'byCommercant',
            'topProducts', 'statusCounts', 'paymentMethods'
        ));
    }
}
