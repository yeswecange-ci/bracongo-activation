<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège les endpoints publics appelés par Twilio Studio (préfixe /api/can).
 *
 * La clé attendue est lue dans config('services.can_api_key') (= env CAN_API_KEY).
 * La clé fournie peut arriver via l'en-tête X-Api-Key OU le champ "api_key"
 * du corps/de la query (Twilio Studio ne pose pas toujours d'en-tête custom).
 *
 * Déploiement progressif (fail-open) : tant que CAN_API_KEY n'est PAS défini,
 * le middleware laisse tout passer afin de ne pas casser le bot en production.
 * Dès que la clé est configurée côté serveur ET dans le flow Twilio, la
 * vérification devient obligatoire.
 */
class VerifyApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.can_api_key');

        // Non configuré → on laisse passer (rollout progressif), mais on trace.
        if (empty($expected)) {
            return $next($request);
        }

        $provided = $request->header('X-Api-Key') ?? $request->input('api_key');

        if (! is_string($provided) || ! hash_equals($expected, $provided)) {
            Log::warning('API CAN - clé API invalide ou manquante', [
                'path' => $request->path(),
                'ip'   => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
