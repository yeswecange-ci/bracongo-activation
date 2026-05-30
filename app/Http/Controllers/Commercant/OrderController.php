<?php

namespace App\Http\Controllers\Commercant;

use App\Http\Controllers\Controller;
use App\Models\LckOrder;
use App\Services\LckNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(protected LckNotificationService $notifications) {}

    public function index(Request $request)
    {
        $query = LckOrder::with('items')->orderByDesc('created_at');

        // Filtres
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_ref', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->paginate(15)->withQueryString();

        $counts = [
            'all'       => LckOrder::count(),
            'received'  => LckOrder::where('status', 'received')->count(),
            'confirmed' => LckOrder::where('status', 'confirmed')->count(),
            'preparing' => LckOrder::where('status', 'preparing')->count(),
            'ready'     => LckOrder::where('status', 'ready')->count(),
            'delivered' => LckOrder::where('status', 'delivered')->count(),
            'cancelled' => LckOrder::where('status', 'cancelled')->count(),
        ];

        return view('commercant.orders.index', compact('orders', 'counts'));
    }

    public function show(string $ref)
    {
        $order = LckOrder::with(['items.product', 'commercant'])
            ->where('order_ref', $ref)
            ->firstOrFail();

        return view('commercant.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, string $ref)
    {
        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,delivered,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $order = LckOrder::where('order_ref', $ref)->firstOrFail();

        $updates = ['status' => $request->status];

        if ($request->notes) {
            $updates['notes'] = $request->notes;
        }

        // Assigner la commercante qui traite la commande
        $updates['commercant_id'] = Auth::guard('commercant')->id();

        match ($request->status) {
            'confirmed' => $updates['confirmed_at'] = now(),
            'ready'     => $updates['ready_at']     = now(),
            'delivered' => $updates['delivered_at'] = now(),
            default     => null,
        };

        $order->update($updates);

        // Notifications client selon le statut
        if ($request->status === 'ready') {
            $this->notifications->notifyCustomerOrderReady($order);
        } elseif ($request->status === 'cancelled') {
            $this->notifications->notifyCustomerOrderCancelled($order, $request->notes ?? '');
        }

        return redirect()
            ->route('commercant.orders.show', $ref)
            ->with('success', "Statut mis à jour : {$order->fresh()->status_label}");
    }

    public function destroy(string $ref)
    {
        $order = LckOrder::where('order_ref', $ref)->firstOrFail();
        $order->items()->delete();
        $order->delete();

        return redirect()->route('commercant.orders.index')
            ->with('success', "Commande {$ref} supprimée.");
    }

    public function export(Request $request)
    {
        $query = LckOrder::with('items')->orderByDesc('created_at');

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->get();

        $filename = 'commandes_lck_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Référence', 'Date', 'Client', 'Téléphone',
                'Produits', 'Total ($)', 'Statut',
            ], ';');

            foreach ($orders as $order) {
                $products = $order->items->map(fn($i) => "{$i->product_name} x{$i->quantity}")->implode(' | ');
                fputcsv($file, [
                    $order->order_ref,
                    $order->created_at->format('d/m/Y H:i'),
                    $order->customer_name ?? '-',
                    $order->customer_phone,
                    $products,
                    number_format($order->total, 2),
                    $order->status_label,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
