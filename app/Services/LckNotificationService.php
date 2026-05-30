<?php

namespace App\Services;

use App\Models\Commercant;
use App\Models\LckOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LckNotificationService
{
    public function __construct(protected WhatsAppService $whatsapp) {}

    // ─────────────────────────────────────────────────────────────
    // Nouvelle commande reçue → notifier toutes les commercantes actives
    // ─────────────────────────────────────────────────────────────
    public function notifyCommercanteNewOrder(LckOrder $order): void
    {
        $order->loadMissing('items');

        $itemsText = '';
        foreach ($order->items as $item) {
            $itemsText .= "• {$item->product_name} × {$item->quantity} = " . number_format($item->subtotal, 2) . " $\n";
        }

        $locationLine = $order->customer_location
            ? "Zone: *{$order->customer_location}*\n"
            : '';

        $message = "🔔 *Nouvelle commande LCK*\n\n"
            . "Référence: *{$order->order_ref}*\n"
            . "Client: " . ($order->customer_name ?? 'Non renseigné') . "\n"
            . "Tél: {$order->customer_phone}\n"
            . $locationLine . "\n"
            . "*Produits:*\n{$itemsText}\n"
            . "*Total: " . number_format($order->total, 2) . " $*\n\n"
            . "👉 Connectez-vous au dashboard pour traiter cette commande.";

        // Cherche les commercants couvrant la zone du client
        $commercantes = $this->resolveCommercantsForOrder($order);

        foreach ($commercantes as $commercante) {
            if ($commercante->phone) {
                $this->whatsapp->sendMessage($commercante->phone, $message);
            }
            $this->sendEmailToCommercante($commercante, $order, $itemsText);
        }

        Log::info('LCK Notification envoyée', [
            'order_ref'   => $order->order_ref,
            'location'    => $order->customer_location,
            'commercants' => $commercantes->pluck('name')->toArray(),
            'broadcast'   => $commercantes->count() > 1 ? 'all' : 'zone',
        ]);
    }

    // Trouve le(s) commercant(s) couvrant la zone du client.
    // Fallback : tous les commercants actifs si aucune correspondance.
    private function resolveCommercantsForOrder(LckOrder $order): \Illuminate\Support\Collection
    {
        $location = strtolower(trim($order->customer_location ?? ''));

        $all = Commercant::where('is_active', true)->get();

        if (!$location) {
            return $all;
        }

        $matched = $all->filter(function (Commercant $c) use ($location) {
            foreach ((array) ($c->zones ?? []) as $zone) {
                $zone = strtolower(trim($zone));
                if ($zone && (str_contains($location, $zone) || str_contains($zone, $location))) {
                    return true;
                }
            }
            return false;
        });

        return $matched->isNotEmpty() ? $matched : $all;
    }

    // ─────────────────────────────────────────────────────────────
    // Paiement en ligne confirmé → notifier le client
    // ─────────────────────────────────────────────────────────────
    public function notifyCustomerPaymentConfirmed(LckOrder $order): void
    {
        $message = "✅ *Paiement reçu — La Clé des Châteaux*\n\n"
            . "Référence commande : *{$order->order_ref}*\n"
            . "Montant payé : *" . number_format($order->amount_paid ?? $order->total, 2) . " \$*\n\n"
            . "Votre commande est en cours de préparation. Vous serez averti(e) dès qu'elle sera en route. 🍷";

        $this->whatsapp->sendMessage($order->customer_phone, $message);
    }

    // ─────────────────────────────────────────────────────────────
    // En préparation → notifier le client
    // ─────────────────────────────────────────────────────────────
    public function notifyCustomerOrderPreparing(LckOrder $order): void
    {
        $message = "🔧 *Votre commande est en préparation !*\n\n"
            . "Référence: *{$order->order_ref}*\n\n"
            . "Notre équipe prépare vos articles. Vous serez averti(e) dès que la commande sera prête. 🍷\n\n"
            . "_La Clé des Châteaux_";

        $this->whatsapp->sendMessage($order->customer_phone, $message);
    }

    // ─────────────────────────────────────────────────────────────
    // En route → notifier le client avant livraison
    // ─────────────────────────────────────────────────────────────
    public function notifyCustomerOrderOnTheWay(LckOrder $order): void
    {
        $message = "🚗 *Votre commande est en route !*\n\n"
            . "Référence: *{$order->order_ref}*\n\n"
            . "Votre livreur est en chemin vers *{$order->customer_location}*.\n"
            . "Restez disponible sur ce numéro. 📞\n\n"
            . "_La Clé des Châteaux_";

        $this->whatsapp->sendMessage($order->customer_phone, $message);
    }

    // ─────────────────────────────────────────────────────────────
    // Confirmation de commande → notifier le client
    // ─────────────────────────────────────────────────────────────
    public function notifyCustomerOrderConfirmed(LckOrder $order): void
    {
        $message = "✅ *Commande confirmée — La Clé des Châteaux*\n\n"
            . "Référence: *{$order->order_ref}*\n"
            . "Total: *" . number_format($order->total, 2) . " $*\n\n"
            . "Notre équipe prépare votre commande. Vous serez averti(e) dès qu'elle sera prête. 🍷";

        $this->whatsapp->sendMessage($order->customer_phone, $message);
    }

    // ─────────────────────────────────────────────────────────────
    // Commande prête → notifier le client
    // ─────────────────────────────────────────────────────────────
    public function notifyCustomerOrderReady(LckOrder $order): void
    {
        $message = "📦 *Votre commande est prête!*\n\n"
            . "Référence: *{$order->order_ref}*\n\n"
            . "Vous pouvez venir la récupérer à:\n"
            . "📍 La Clé des Châteaux\n"
            . "Boulevard du 30 Juin, Commune de Gombe\n"
            . "Kinshasa, RDC\n\n"
            . "🕐 Horaires: Lun–Sam 9h–19h\n\n"
            . "À très bientôt! 🍷";

        $result = $this->whatsapp->sendMessage($order->customer_phone, $message);

        if (!$result || !$result['success']) {
            Log::warning('LCK: Échec notification WhatsApp client commande prête', [
                'order_ref' => $order->order_ref,
                'phone'     => $order->customer_phone,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Commande annulée → notifier le client
    // ─────────────────────────────────────────────────────────────
    public function notifyCustomerOrderCancelled(LckOrder $order, string $reason = ''): void
    {
        $message = "❌ *Commande annulée — La Clé des Châteaux*\n\n"
            . "Référence: *{$order->order_ref}*\n";

        if ($reason) {
            $message .= "Motif: {$reason}\n";
        }

        $message .= "\nPour toute question, contactez-nous directement.\n"
            . "📍 Boulevard du 30 Juin, Gombe — Kinshasa";

        $this->whatsapp->sendMessage($order->customer_phone, $message);
    }

    // ─────────────────────────────────────────────────────────────
    // Email interne à la commercante
    // ─────────────────────────────────────────────────────────────
    private function sendEmailToCommercante(Commercant $commercante, LckOrder $order, string $itemsText): void
    {
        try {
            Mail::raw(
                "Nouvelle commande reçue sur La Clé des Châteaux\n\n"
                . "Référence: {$order->order_ref}\n"
                . "Client: " . ($order->customer_name ?? 'Non renseigné') . "\n"
                . "Téléphone: {$order->customer_phone}\n\n"
                . "Produits commandés:\n{$itemsText}\n"
                . "Total: " . number_format($order->total, 2) . " $\n\n"
                . "Connectez-vous au dashboard pour traiter cette commande:\n"
                . url('/commercant/orders/' . $order->order_ref),
                fn($mail) => $mail
                    ->to($commercante->email, $commercante->name)
                    ->subject("🍷 Nouvelle commande {$order->order_ref} — La Clé des Châteaux")
            );
        } catch (\Exception $e) {
            Log::error('LCK: Échec envoi email commercante', [
                'commercante' => $commercante->email,
                'order_ref'   => $order->order_ref,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
