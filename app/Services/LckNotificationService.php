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

        $message = "🔔 *Nouvelle commande LCK*\n\n"
            . "Référence: *{$order->order_ref}*\n"
            . "Client: " . ($order->customer_name ?? 'Non renseigné') . "\n"
            . "Tél: {$order->customer_phone}\n\n"
            . "*Produits:*\n{$itemsText}\n"
            . "*Total: " . number_format($order->total, 2) . " $*\n\n"
            . "👉 Connectez-vous au dashboard pour traiter cette commande.";

        $commercantes = Commercant::where('is_active', true)->get();

        foreach ($commercantes as $commercante) {
            // Notification WhatsApp
            if ($commercante->phone) {
                $this->whatsapp->sendMessage($commercante->phone, $message);
            }

            // Notification email
            $this->sendEmailToCommercante($commercante, $order, $itemsText);
        }
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
