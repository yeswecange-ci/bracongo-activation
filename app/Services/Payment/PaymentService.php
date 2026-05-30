<?php

namespace App\Services\Payment;

use App\Models\LckOrder;
use Illuminate\Support\Facades\Log;

/**
 * Façade PaymentService — driver pattern pluggable
 *
 * Pour ajouter un nouveau provider :
 *   1. Créer app/Services/Payment/MonProviderDriver.php qui implémente PaymentDriverInterface
 *   2. L'ajouter dans $drivers ci-dessous
 *   3. Ajouter les variables .env correspondantes
 */
class PaymentService
{
    private array $drivers = [
        'cash_on_delivery' => CashOnDeliveryDriver::class,
        'cinetpay'         => CinetPayDriver::class,
        // 'orange_money'  => OrangeMoneyDriver::class,   // à venir
        // 'airtel_money'  => AirtelMoneyDriver::class,   // à venir
        // 'mpesa'         => MPesaDriver::class,          // à venir
    ];

    public function driver(string $name): PaymentDriverInterface
    {
        $class = $this->drivers[$name] ?? null;

        if (!$class) {
            Log::warning("PaymentService: driver inconnu '{$name}', fallback cash_on_delivery");
            $class = CashOnDeliveryDriver::class;
        }

        return new $class();
    }

    /**
     * Résout le driver à partir du payment_method de la commande.
     * "cash_on_delivery" → CashOnDeliveryDriver
     * "online"           → driver configuré dans PAYMENT_ONLINE_DRIVER (.env)
     */
    public function forOrder(LckOrder $order): PaymentDriverInterface
    {
        if ($order->payment_method === 'cash_on_delivery') {
            return $this->driver('cash_on_delivery');
        }

        // Pour "online", on utilise le driver configuré dans .env
        $onlineDriver = config('services.payment.online_driver', 'cinetpay');
        return $this->driver($onlineDriver);
    }

    /**
     * Marque une commande comme payée (appelé depuis le webhook).
     */
    public function markAsPaid(LckOrder $order, ?string $reference = null, ?float $amount = null): void
    {
        $order->update([
            'payment_status'    => 'paid',
            'payment_reference' => $reference ?? $order->payment_reference,
            'amount_paid'       => $amount ?? $order->total,
            'paid_at'           => now(),
        ]);

        Log::info('LCK Payment confirmed', [
            'order_ref' => $order->order_ref,
            'reference' => $reference,
            'amount'    => $amount,
        ]);
    }

    /**
     * Liste des méthodes de paiement disponibles pour l'affichage.
     */
    public static function availableMethods(): array
    {
        return [
            'cash_on_delivery' => ['label' => 'À la livraison (cash)', 'emoji' => '💵'],
            'online'           => ['label' => 'Paiement Mobile Money',  'emoji' => '📱'],
        ];
    }
}
