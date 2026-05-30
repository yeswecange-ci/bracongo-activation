<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LckOrder;
use App\Services\LckNotificationService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LckPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $payments,
        protected LckNotificationService $notifications,
    ) {}

    // ─────────────────────────────────────────────────────────────
    // POST /api/lck/payment/initiate
    // Appelé par Twilio quand le client choisit "payer en ligne"
    // ─────────────────────────────────────────────────────────────
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'order_ref' => 'required|string',
        ]);

        $order = LckOrder::where('order_ref', $request->order_ref)->firstOrFail();

        if ($order->payment_method === 'cash_on_delivery') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est à régler en espèces à la livraison.',
            ], 400);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande est déjà payée.',
            ], 409);
        }

        $driver = $this->payments->forOrder($order);
        $result = $driver->initiate($order);

        return response()->json([
            'success'     => $result['success'],
            'payment_url' => $result['payment_url'],
            'reference'   => $result['reference'],
            'message'     => $result['message'],
        ], $result['success'] ? 200 : 500);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /api/lck/payment/callback
    // Webhook reçu de CinetPay / Orange Money / Airtel après paiement
    // ─────────────────────────────────────────────────────────────
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('LCK Payment callback received', $payload);

        // Retrouver la commande via la référence de transaction
        $reference = $payload['cpm_trans_id']     // CinetPay
            ?? $payload['transaction_id']           // générique
            ?? $payload['reference']                // fallback
            ?? null;

        if (!$reference) {
            return response()->json(['status' => 'error', 'message' => 'No reference'], 400);
        }

        $order = LckOrder::where('payment_reference', $reference)->first();

        if (!$order) {
            Log::warning('LCK Payment callback: order not found', ['reference' => $reference]);
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Déléguer la vérification au driver correspondant
        $driver    = $this->payments->forOrder($order);
        $confirmed = $driver->handleCallback($payload);

        if ($confirmed) {
            $amount = (float) ($payload['cpm_amount'] ?? $payload['amount'] ?? $order->total);
            $this->payments->markAsPaid($order, $reference, $amount);

            // Notifier le client que son paiement est confirmé
            $this->notifications->notifyCustomerPaymentConfirmed($order->fresh());

            // Notifier les commercants que le paiement est reçu (PAS notifyCommercanteNewOrder
            // qui re-enverrait "nouvelle commande" — la commande existe déjà)
            $this->notifications->notifyCommercantePaymentReceived($order->fresh());

            return response()->json(['status' => 'ok']);
        }

        // Paiement échoué
        $order->update(['payment_status' => 'failed']);
        Log::warning('LCK Payment callback: payment failed', ['reference' => $reference]);

        return response()->json(['status' => 'failed']);
    }

    // ─────────────────────────────────────────────────────────────
    // POST /api/lck/payment/verify
    // Vérification manuelle du statut d'un paiement
    // ─────────────────────────────────────────────────────────────
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['reference' => 'required|string']);

        $order = LckOrder::where('payment_reference', $request->reference)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Commande introuvable'], 404);
        }

        $driver = $this->payments->forOrder($order);
        $result = $driver->verify($request->reference);

        if ($result['success'] && $result['status'] === 'paid' && $order->payment_status !== 'paid') {
            $this->payments->markAsPaid($order, $request->reference, $result['amount']);
        }

        return response()->json([
            'success'        => $result['success'],
            'payment_status' => $order->fresh()->payment_status,
            'order_ref'      => $order->order_ref,
        ]);
    }
}
