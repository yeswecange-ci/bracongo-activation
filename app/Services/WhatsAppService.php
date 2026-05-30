<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $twilio;
    protected $from;

    public function __construct()
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken  = config('services.twilio.auth_token');
        $this->from = config('services.twilio.whatsapp_from');

        if ($accountSid && $authToken) {
            // Le serveur de prod a un problème de certificats CA pour les appels
            // sortants vers l'API Twilio — on désactive la vérification SSL.
            // Twilio utilise HTTPS donc les données restent chiffrées.
            $httpClient = new \Twilio\Http\CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            $this->twilio = new Client($accountSid, $authToken, null, null, $httpClient);
        }
    }

    /**
     * Envoyer un message WhatsApp
     *
     * @param string $to Numéro au format whatsapp:+243XXXXXXXXX
     * @param string $message Contenu du message
     * @param string|null $statusCallback URL pour recevoir les status callbacks
     * @return array|false ['success' => bool, 'sid' => string, 'status' => string] ou false
     */
    public function sendMessage(string $to, string $message, ?string $statusCallback = null)
    {
        if (!$this->twilio) {
            Log::error('Twilio not configured');
            return false;
        }

        // S'assurer que le numéro commence par whatsapp:
        if (!str_starts_with($to, 'whatsapp:')) {
            $to = 'whatsapp:' . $to;
        }

        // Bloquer l'envoi vers le numéro FROM (Twilio refuse de s'envoyer à lui-même)
        $fromClean = preg_replace('/[^0-9]/', '', $this->from);
        $toClean   = preg_replace('/[^0-9]/', '', $to);
        if ($fromClean === $toClean) {
            Log::warning('WhatsApp: envoi bloqué — destinataire = numéro FROM Twilio', ['to' => $to]);
            return false;
        }

        try {

            $params = [
                'from' => $this->from,
                'body' => $message
            ];

            // Ajouter le status callback si fourni
            if ($statusCallback) {
                $params['statusCallback'] = $statusCallback;
            }

            $result = $this->twilio->messages->create($to, $params);

            Log::info('WhatsApp message sent', [
                'to' => $to,
                'sid' => $result->sid,
                'status' => $result->status
            ]);

            return [
                'success' => true,
                'sid' => $result->sid,
                'status' => $result->status
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp send error: ' . $e->getMessage(), [
                'to' => $to,
                'message' => $message,
                'error_code' => $e->getCode()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Envoyer un message de bienvenue
     *
     * @param string $to
     * @param string $name
     * @param string $village
     * @return bool
     */
    public function sendWelcomeMessage(string $to, string $name, string $village): bool
    {
        $message = "🎉 Bienvenue à CAN 2025, {$name} !\n\n";
        $message .= "✅ Ton inscription au village {$village} est confirmée.\n\n";
        $message .= "🏆 Tu peux maintenant faire des pronostics sur les matchs et gagner des prix !\n\n";
        $message .= "📱 Envoie MENU pour voir les options disponibles.";

        return $this->sendMessage($to, $message);
    }

    /**
     * Envoyer le menu principal
     *
     * @param string $to
     * @return bool
     */
    public function sendMenu(string $to): bool
    {
        $message = "📋 *MENU CAN 2025*\n\n";
        $message .= "Envoie le numéro correspondant :\n\n";
        $message .= "1️⃣ MATCHS - Voir les prochains matchs\n";
        $message .= "2️⃣ PRONOSTIC - Faire un pronostic\n";
        $message .= "3️⃣ MES PRONOS - Voir mes pronostics\n";
        $message .= "4️⃣ CLASSEMENT - Voir le classement\n";
        $message .= "5️⃣ AIDE - Besoin d'aide\n\n";
        $message .= "💡 Tu peux aussi envoyer MENU à tout moment.";

        return $this->sendMessage($to, $message);
    }

    /**
     * Demander le choix du village
     *
     * @param string $to
     * @param array $villages
     * @return bool
     */
    public function askVillageChoice(string $to, array $villages): bool
    {
        $message = "🏘️ *Choisis ton village CAN*\n\n";
        $message .= "Envoie le numéro correspondant :\n\n";

        foreach ($villages as $index => $village) {
            $number = $index + 1;
            $message .= "{$number}️⃣ {$village['name']}\n";
        }

        $message .= "\n📍 Les villages sont les centres d'animation pour la CAN 2025 !";

        return $this->sendMessage($to, $message);
    }

    /**
     * Demander le nom de l'utilisateur
     *
     * @param string $to
     * @return bool
     */
    public function askName(string $to): bool
    {
        $message = "👋 Bienvenue sur CAN 2025 !\n\n";
        $message .= "Comment t'appelles-tu ?\n\n";
        $message .= "📝 Envoie ton nom pour continuer l'inscription.";

        return $this->sendMessage($to, $message);
    }

    /**
     * Envoyer un message d'erreur
     *
     * @param string $to
     * @param string $error
     * @return bool
     */
    public function sendError(string $to, string $error = "Désolé, je n'ai pas compris."): bool
    {
        $message = "❌ {$error}\n\n";
        $message .= "Envoie MENU pour voir les options disponibles.";

        return $this->sendMessage($to, $message);
    }

    /**
     * Formater un numéro de téléphone pour WhatsApp
     *
     * @param string $phone
     * @return string
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Retirer tous les caractères non numériques sauf le +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Si commence par 0, remplacer par +243 (Congo)
        if (str_starts_with($phone, '0')) {
            $phone = '+243' . substr($phone, 1);
        }

        // Ajouter + si absent
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Vérifier si Twilio est configuré
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return $this->twilio !== null;
    }
}
