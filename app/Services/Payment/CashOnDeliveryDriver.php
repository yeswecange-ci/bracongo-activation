<?php

namespace App\Services\Payment;

use App\Models\LckOrder;

class CashOnDeliveryDriver implements PaymentDriverInterface
{
    public function initiate(LckOrder $order): array
    {
        // Pas de paiement en ligne — la commande est directement confirmée
        $order->update([
            'payment_status' => 'pending', // sera "paid" lors de la livraison physique
        ]);

        return [
            'success'     => true,
            'payment_url' => null,
            'reference'   => null,
            'message'     => "✅ *Commande confirmée !*\n\n"
                . "Référence : *{$order->order_ref}*\n"
                . "Total : *" . number_format($order->total, 2) . " \$*\n"
                . "Paiement : 💵 *À la livraison*\n\n"
                . "Notre équipe prépare votre commande. Vous serez averti(e) dès qu'elle sera en route. 🍷",
        ];
    }

    public function verify(string $reference): array
    {
        return ['success' => true, 'status' => 'pending', 'amount' => null];
    }

    public function handleCallback(array $payload): bool
    {
        return false; // Pas de callback pour le cash
    }
}
