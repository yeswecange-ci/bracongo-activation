<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LckOrder;
use Illuminate\Http\Request;

class LckOrderController extends Controller
{
    public function index(Request $request)
    {
        // Export CSV
        if ($request->boolean('export')) {
            return $this->exportCsv($request);
        }

        $query = LckOrder::with(['items', 'commercant'])->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_ref', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'all'       => LckOrder::count(),
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

    public function updateStatus(Request $request, string $ref)
    {
        $request->validate([
            'status' => 'required|in:received,confirmed,preparing,ready,delivered,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $order = LckOrder::where('order_ref', $ref)->firstOrFail();

        $updates = ['status' => $request->status];
        if ($request->filled('notes')) $updates['notes'] = $request->notes;

        match ($request->status) {
            'confirmed' => $updates['confirmed_at'] = now(),
            'ready'     => $updates['ready_at']     = now(),
            'delivered' => $updates['delivered_at'] = now(),
            default     => null,
        };

        // Cash on delivery → paiement confirmé automatiquement à la livraison
        if ($request->status === 'delivered'
            && $order->payment_method === 'cash_on_delivery'
            && $order->payment_status === 'pending') {
            $updates['payment_status'] = 'paid';
            $updates['amount_paid']    = $order->total;
            $updates['paid_at']        = now();
        }

        $order->update($updates);

        return redirect()->route('admin.lck.orders.show', $ref)
            ->with('success', 'Statut mis à jour.');
    }

    public function destroy(string $ref)
    {
        $order = LckOrder::where('order_ref', $ref)->firstOrFail();
        $order->items()->delete();
        $order->delete();

        return redirect()->route('admin.lck.orders.index')
            ->with('success', "Commande {$ref} supprimée.");
    }

    public function destroyAll()
    {
        $count = LckOrder::count();
        \App\Models\LckOrderItem::truncate();
        \App\Models\LckCartSession::truncate();
        LckOrder::truncate();

        \Illuminate\Support\Facades\Log::warning("LCK: toutes les commandes supprimées par l'admin ({$count} commandes).");

        return redirect()->route('admin.lck.orders.index')
            ->with('success', "{$count} commande(s) supprimée(s). Les données de test ont été effacées.");
    }

    private function exportCsv(Request $request)
    {
        $query = LckOrder::with(['items', 'commercant'])->orderByDesc('created_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $orders = $query->get();

        $filename = 'lck-commandes-' . now()->format('Ymd-His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($handle, ['Référence', 'Client', 'Téléphone', 'Statut', 'Total ($)', 'Articles', 'Traitée par', 'Date']);

            foreach ($orders as $order) {
                $articles = $order->items->map(fn($i) => "{$i->product_name} x{$i->quantity}")->implode(' | ');
                fputcsv($handle, [
                    $order->order_ref,
                    $order->customer_name ?? '',
                    $order->customer_phone,
                    $order->status_label,
                    number_format($order->total, 2),
                    $articles,
                    $order->commercant?->name ?? '',
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
