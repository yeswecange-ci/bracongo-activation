<?php

namespace App\Services\Payment;

use App\Models\LckOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Driver CinetPay — supporte Orange Money, Airtel Money, M-Pesa (DRC)
 * Docs : https://docs.cinetpay.com
 *
 * Variables .env à configurer :
 *   CINETPAY_API_KEY=
 *   CINETPAY_SITE_ID=
 *   CINETPAY_NOTIFY_URL=https://can-wabracongo.ywcdigital.com/api/lck/payment/callback
 */
class CinetPayDriver implements PaymentDriverInterface
{
    private string $apiKey;
    private string $siteId;
    private string $notifyUrl;
    private string $baseUrl = 'https://api-checkout.cinetpay.com/v2';

    public function __construct()
    {
        $this->apiKey    = config('services.cinetpay.api_key', '');
        $this->siteId    = config('services.cinetpay.site_id', '');
        $this->notifyUrl = config('services.cinetpay.notify_url',
            url('/api/lck/payment/callback'));
    }

    public function initiate(LckOrder $order): array
    {
        try {
            $transactionId = 'LCK-' . $order->id . '-' . time();

            $payload = [
                'apikey'                => $this->apiKey,
                'site_id'               => $this->siteId,
                'transaction_id'        => $transactionId,
                'amount'                => (int) ceil($order->total),
                'currency'              => 'USD',
                'description'           => "Commande {$order->order_ref} — La Clé des Châteaux",
                'customer_name'         => $order->customer_name ?? 'Client',
                'customer_phone_number' => preg_replace('/\D/', '', $order->customer_phone),
                'notify_url'            => $this->notifyUrl,
                'return_url'            => url('/'),
                'channels'              => 'MOBILE_MONEY',
                'metadata'              => json_encode(['order_ref' => $order->order_ref]),
            ];

            $response = Http::post("{$this->baseUrl}/payment", $payload);
            $data     = $response->json();

            if (($data['code'] ?? '') === '201') {
                $paymentUrl = $data['data']['payment_url'] ?? null;

                $order->update([
                    'payment_reference' => $transactionId,
                    'payment_status'    => 'pending',
                ]);

                return [
                    'success'     => true,
                    'payment_url' => $paymentUrl,
                    'reference'   => $transactionId,
                    'message'     => "💳 *Paiement en ligne*\n\n"
                        . "Référence : *{$order->order_ref}*\n"
                        . "Montant : *" . number_format($order->total, 2) . " \$*\n\n"
                        . "👉 Cliquez ici pour payer via Mobile Money :\n{$paymentUrl}\n\n"
                        . "⏰ Ce lien est valable 30 minutes.\n"
                        . "Votre commande sera confirmée automatiquement après paiement. 🍷",
                ];
            }

            Log::error('CinetPay initiate failed', ['response' => $data]);
            return ['success' => false, 'payment_url' => null, 'reference' => null,
                    'message' => "❌ Erreur lors de l'initialisation du paiement. Réessayez ou choisissez le paiement à la livraison."];

        } catch (\Exception $e) {
            Log::error('CinetPay exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'payment_url' => null, 'reference' => null,
                    'message' => "❌ Service de paiement indisponible. Veuillez réessayer."];
        }
    }

    public function verify(string $reference): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/payment/check", [
                'apikey'         => $this->apiKey,
                'site_id'        => $this->siteId,
                'transaction_id' => $reference,
            ]);
            $data = $response->json();

            $status = match ($data['data']['status'] ?? '') {
                'ACCEPTED' => 'paid',
                'REFUSED'  => 'failed',
                default    => 'pending',
            };

            return [
                'success' => true,
                'status'  => $status,
                'amount'  => (float) ($data['data']['amount'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'status' => 'pending', 'amount' => null];
        }
    }

    public function handleCallback(array $payload): bool
    {
        // CinetPay envoie cpm_trans_id et cpm_result
        $transactionId = $payload['cpm_trans_id'] ?? null;
        $result        = $payload['cpm_result'] ?? null;

        if (!$transactionId) return false;

        return $result === '00'; // "00" = succès chez CinetPay
    }
}
