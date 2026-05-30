<?php

namespace App\Services\Payment;

use App\Models\LckOrder;

interface PaymentDriverInterface
{
    /**
     * Initie un paiement et retourne les infos nécessaires.
     *
     * @return array{
     *   success: bool,
     *   payment_url: string|null,   // lien de paiement à envoyer au client (null si cash)
     *   reference: string|null,     // référence externe générée par le provider
     *   message: string,            // message WhatsApp à envoyer au client
     * }
     */
    public function initiate(LckOrder $order): array;

    /**
     * Vérifie le statut d'un paiement via la référence externe.
     *
     * @return array{ success: bool, status: string, amount: float|null }
     */
    public function verify(string $reference): array;

    /**
     * Traite un callback/webhook entrant du provider.
     * Retourne true si le paiement est confirmé.
     */
    public function handleCallback(array $payload): bool;
}
