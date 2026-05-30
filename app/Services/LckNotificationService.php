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

        $orderUrl  = config('app.url') . '/commercant/orders/' . $order->order_ref;

        $itemsText = '';
        foreach ($order->items as $item) {
            $itemsText .= "• {$item->product_name} × {$item->quantity} = " . number_format($item->subtotal, 2) . " $\n";
        }

        $locationLine = $order->customer_location
            ? "📍 Zone : *{$order->customer_location}*\n"
            : '';

        $paymentLine = $order->payment_method === 'cash_on_delivery'
            ? "💵 Paiement : À la livraison\n"
            : "📱 Paiement : Mobile Money (" . $order->payment_status_label . ")\n";

        $whatsappMessage = "🔔 *Nouvelle commande — La Clé des Châteaux*\n\n"
            . "Référence : *{$order->order_ref}*\n"
            . "Client : " . ($order->customer_name ?? 'Non renseigné') . "\n"
            . "Tél : {$order->customer_phone}\n"
            . $locationLine
            . $paymentLine . "\n"
            . "*Produits :*\n{$itemsText}\n"
            . "*Total : " . number_format($order->total, 2) . " $*\n\n"
            . "👉 Traiter la commande :\n{$orderUrl}";

        // Cherche les commercants couvrant la zone du client
        $commercantes = $this->resolveCommercantsForOrder($order);

        foreach ($commercantes as $commercante) {
            if ($commercante->phone) {
                $this->whatsapp->sendMessage($commercante->phone, $whatsappMessage);
            }
            $this->sendEmailToCommercante($commercante, $order, $itemsText, $orderUrl);
        }

        Log::info('LCK Notification envoyée', [
            'order_ref'   => $order->order_ref,
            'location'    => $order->customer_location,
            'commercants' => $commercantes->pluck('name')->toArray(),
            'broadcast'   => $commercantes->count() > 1 ? 'all (aucune zone)' : 'zone ciblée',
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
    private function sendEmailToCommercante(Commercant $commercante, LckOrder $order, string $itemsText, string $orderUrl): void
    {
        try {
            $locationLine = $order->customer_location
                ? "<tr><td style='color:#666;padding:4px 0'>Zone</td><td style='font-weight:600'>{$order->customer_location}</td></tr>"
                : '';

            $paymentLine = $order->payment_method === 'cash_on_delivery'
                ? '💵 À la livraison'
                : '📱 Mobile Money (' . $order->payment_status_label . ')';

            $itemsHtml = '';
            foreach ($order->items as $item) {
                $itemsHtml .= "<tr>
                    <td style='padding:6px 8px;border-bottom:1px solid #f0f0f0'>{$item->product_name}</td>
                    <td style='padding:6px 8px;text-align:center;border-bottom:1px solid #f0f0f0'>×{$item->quantity}</td>
                    <td style='padding:6px 8px;text-align:right;font-weight:700;border-bottom:1px solid #f0f0f0'>" . number_format($item->subtotal, 2) . " $</td>
                </tr>";
            }

            $html = "<!DOCTYPE html><html lang='fr'><head><meta charset='UTF-8'></head><body style='font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:0'>
<div style='max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1)'>

  <div style='background:#1a1a1a;padding:24px 32px;text-align:center'>
    <h1 style='color:#c9a84c;margin:0;font-size:22px'>🍷 La Clé des Châteaux</h1>
    <p style='color:#999;margin:6px 0 0;font-size:13px'>Nouvelle commande reçue</p>
  </div>

  <div style='padding:28px 32px'>
    <p style='color:#333;margin:0 0 20px'>Bonjour <strong>{$commercante->name}</strong>,</p>
    <p style='color:#333;margin:0 0 20px'>Une nouvelle commande vous a été assignée. Traitez-la dès que possible.</p>

    <div style='background:#fffbeb;border-left:4px solid #c9a84c;padding:16px 20px;border-radius:4px;margin-bottom:24px'>
      <table style='width:100%;border-collapse:collapse'>
        <tr><td style='color:#666;padding:4px 0;width:140px'>Référence</td><td style='font-family:monospace;font-weight:900;font-size:18px;color:#1a1a1a'>{$order->order_ref}</td></tr>
        <tr><td style='color:#666;padding:4px 0'>Client</td><td style='font-weight:600'>" . ($order->customer_name ?? 'Non renseigné') . "</td></tr>
        <tr><td style='color:#666;padding:4px 0'>Téléphone</td><td style='font-weight:600'>{$order->customer_phone}</td></tr>
        {$locationLine}
        <tr><td style='color:#666;padding:4px 0'>Paiement</td><td style='font-weight:600'>{$paymentLine}</td></tr>
      </table>
    </div>

    <h3 style='color:#333;margin:0 0 12px;font-size:15px'>Articles commandés</h3>
    <table style='width:100%;border-collapse:collapse;margin-bottom:16px'>
      <thead>
        <tr style='background:#f9f9f9'>
          <th style='padding:8px;text-align:left;font-size:12px;color:#666;text-transform:uppercase'>Produit</th>
          <th style='padding:8px;text-align:center;font-size:12px;color:#666;text-transform:uppercase'>Qté</th>
          <th style='padding:8px;text-align:right;font-size:12px;color:#666;text-transform:uppercase'>Sous-total</th>
        </tr>
      </thead>
      <tbody>{$itemsHtml}</tbody>
      <tfoot>
        <tr style='background:#1a1a1a'>
          <td colspan='2' style='padding:12px 8px;color:#c9a84c;font-weight:700;font-size:15px;text-transform:uppercase'>Total</td>
          <td style='padding:12px 8px;color:#fff;font-weight:900;font-size:18px;text-align:right'>" . number_format($order->total, 2) . " $</td>
        </tr>
      </tfoot>
    </table>

    <div style='text-align:center;margin:28px 0'>
      <a href='{$orderUrl}'
         style='display:inline-block;background:#c9a84c;color:#fff;text-decoration:none;padding:14px 36px;border-radius:6px;font-weight:700;font-size:15px'>
        👉 Voir et traiter la commande
      </a>
    </div>

    <p style='color:#999;font-size:12px;text-align:center;margin:0'>
      Ou copiez ce lien : <a href='{$orderUrl}' style='color:#c9a84c'>{$orderUrl}</a>
    </p>
  </div>

  <div style='background:#f9f9f9;padding:16px 32px;text-align:center;border-top:1px solid #eee'>
    <p style='color:#aaa;font-size:11px;margin:0'>La Clé des Châteaux — Kinshasa, RDC</p>
  </div>
</div>
</body></html>";

            Mail::html($html, fn($mail) => $mail
                ->to($commercante->email, $commercante->name)
                ->subject("🔔 Nouvelle commande {$order->order_ref} — La Clé des Châteaux")
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
