<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConversationSession;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioStudioController extends Controller
{
    /**
     * Endpoint: POST /api/can/scan
     * Log initial du scan QR code ou contact direct
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'source_type' => 'required|string',
            'source_detail' => 'required|string',
            'timestamp' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        // Créer ou mettre à jour la session de conversation
        $session = ConversationSession::updateOrCreate(
            ['phone' => $phone],
            [
                'state' => ConversationSession::STATE_SCAN,
                'data' => [
                    'source_type' => $validated['source_type'],
                    'source_detail' => $validated['source_detail'],
                    'scan_timestamp' => $validated['timestamp'] ?? now()->toDateTimeString(),
                ],
                'last_activity' => now(),
            ]
        );

        Log::info('Twilio Studio - Scan logged', [
            'phone' => $phone,
            'source' => $validated['source_type'] . ' / ' . $validated['source_detail'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scan logged successfully',
            'session_id' => $session->id,
        ]);
    }

    /**
     * Endpoint: POST /api/can/optin
     * Log de l'opt-in (réponse OUI)
     */
    public function optin(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        $session = ConversationSession::where('phone', $phone)->first();

        if ($session) {
            $session->update([
                'state' => ConversationSession::STATE_OPT_IN,
                'last_activity' => now(),
            ]);
        }

        Log::info('Twilio Studio - Opt-in confirmed', ['phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'Opt-in logged successfully',
        ]);
    }

    /**
     * Endpoint: POST /api/can/inscription
     * Inscription finale avec nom et création de l'utilisateur
     */
    public function inscription(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string|min:2',
            'source_type' => 'required|string',
            'source_detail' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        // Vérifier si l'utilisateur existe déjà
        $user = User::where('phone', $phone)->first();

        if ($user) {
            // Utilisateur déjà inscrit - mise à jour
            $user->update([
                'name' => ucwords(strtolower($validated['name'])),
                'source_type' => $validated['source_type'],
                'source_detail' => $validated['source_detail'],
                'registration_status' => 'INSCRIT',
                'opted_in_at' => now(),
                'is_active' => true,
            ]);

            Log::info('Twilio Studio - User updated', [
                'user_id' => $user->id,
                'phone' => $phone,
            ]);
        } else {
            // Nouvel utilisateur - créer avec village par défaut
            $defaultVillage = Village::where('is_active', true)->first();

            if (!$defaultVillage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active village available',
                ], 400);
            }

            $user = User::create([
                'name' => ucwords(strtolower($validated['name'])),
                'phone' => $phone,
                'village_id' => $defaultVillage->id, // Attribution temporaire
                'source_type' => $validated['source_type'],
                'source_detail' => $validated['source_detail'],
                'scan_timestamp' => $validated['timestamp'] ?? now(),
                'registration_status' => 'INSCRIT',
                'opted_in_at' => now(),
                'is_active' => true,
            ]);

            Log::info('Twilio Studio - New user registered', [
                'user_id' => $user->id,
                'phone' => $phone,
                'source' => $validated['source_type'] . ' / ' . $validated['source_detail'],
            ]);
        }

        // Mettre à jour la session
        $session = ConversationSession::where('phone', $phone)->first();
        if ($session) {
            $session->update([
                'state' => ConversationSession::STATE_REGISTERED,
                'user_id' => $user->id,
                'last_activity' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $user->id,
            'name' => $user->name,
        ]);
    }

    /**
     * Endpoint: POST /api/can/refus
     * Log du refus d'opt-in
     */
    public function refus(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        $session = ConversationSession::where('phone', $phone)->first();
        if ($session) {
            $session->update([
                'state' => ConversationSession::STATE_REFUS,
                'last_activity' => now(),
            ]);
        }

        Log::info('Twilio Studio - Opt-in refused', ['phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'Refusal logged successfully',
        ]);
    }

    /**
     * Endpoint: POST /api/can/stop
     * Désinscription (STOP)
     */
    public function stop(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        // Désactiver l'utilisateur s'il existe
        $user = User::where('phone', $phone)->first();
        if ($user) {
            $user->update([
                'is_active' => false,
                'registration_status' => 'STOP',
            ]);
        }

        $session = ConversationSession::where('phone', $phone)->first();
        if ($session) {
            $session->update([
                'state' => ConversationSession::STATE_STOP,
                'last_activity' => now(),
            ]);
        }

        Log::info('Twilio Studio - User stopped', ['phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'User unsubscribed successfully',
        ]);
    }

    /**
     * Endpoint: POST /api/can/abandon
     * Abandon du processus d'inscription
     */
    public function abandon(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        $session = ConversationSession::where('phone', $phone)->first();
        if ($session) {
            $session->update([
                'state' => ConversationSession::STATE_ABANDON,
                'last_activity' => now(),
            ]);
        }

        Log::info('Twilio Studio - Registration abandoned', ['phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'Abandonment logged successfully',
        ]);
    }

    /**
     * Endpoint: POST /api/can/timeout
     * Timeout pendant le processus
     */
    public function timeout(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        $session = ConversationSession::where('phone', $phone)->first();
        if ($session) {
            $session->update([
                'state' => ConversationSession::STATE_TIMEOUT,
                'last_activity' => now(),
            ]);
        }

        Log::info('Twilio Studio - Timeout', [
            'phone' => $phone,
            'status' => $validated['status'] ?? 'UNKNOWN',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Timeout logged successfully',
        ]);
    }

    /**
     * Endpoint: POST /api/can/error
     * Erreur de livraison ou autre
     */
    public function error(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'status' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);

        Log::error('Twilio Studio - Delivery error', [
            'phone' => $phone,
            'status' => $validated['status'] ?? 'DELIVERY_FAILED',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Error logged successfully',
        ]);
    }

    /**
     * Formater le numéro de téléphone
     */
    private function formatPhone(string $phone): string
    {
        // Retirer "whatsapp:" si présent
        $phone = str_replace('whatsapp:', '', $phone);

        // Retirer tous les caractères non numériques sauf le +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Ajouter + si absent
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
