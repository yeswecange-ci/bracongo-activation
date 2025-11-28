<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'state',
        'data',
        'user_id',
        'last_activity',
    ];

    protected $casts = [
        'data' => 'array',
        'last_activity' => 'datetime',
    ];

    // États possibles de la conversation
    const STATE_IDLE = 'idle';
    const STATE_AWAITING_NAME = 'awaiting_name';
    const STATE_AWAITING_VILLAGE = 'awaiting_village';
    const STATE_REGISTERED = 'registered';
    const STATE_AWAITING_PRONOSTIC = 'awaiting_pronostic';

    // États du flow de pronostic
    const STATE_AWAITING_MATCH_CHOICE = 'awaiting_match_choice';
    const STATE_AWAITING_SCORE_A = 'awaiting_score_a';
    const STATE_AWAITING_SCORE_B = 'awaiting_score_b';

    // États Twilio Studio Flow
    const STATE_SCAN = 'SCAN';
    const STATE_OPT_IN = 'OPT_IN';
    const STATE_REFUS = 'REFUS';
    const STATE_STOP = 'STOP';
    const STATE_ABANDON = 'ABANDON';
    const STATE_TIMEOUT = 'TIMEOUT';

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mettre à jour l'état de la session
     */
    public function setState(string $state, array $data = []): void
    {
        $this->update([
            'state' => $state,
            'data' => array_merge($this->data ?? [], $data),
            'last_activity' => now(),
        ]);
    }

    /**
     * Obtenir une donnée de la session
     */
    public function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Réinitialiser la session
     */
    public function reset(): void
    {
        $this->update([
            'state' => self::STATE_IDLE,
            'data' => null,
            'last_activity' => now(),
        ]);
    }

    /**
     * Vérifier si la session est expirée (plus de 30 minutes)
     */
    public function isExpired(): bool
    {
        return $this->last_activity->diffInMinutes(now()) > 30;
    }

    /**
     * Obtenir ou créer une session pour un numéro
     */
    public static function getOrCreate(string $phone): self
    {
        return self::firstOrCreate(
            ['phone' => $phone],
            [
                'state' => self::STATE_IDLE,
                'last_activity' => now(),
            ]
        );
    }

    /**
     * Nettoyer les sessions expirées (commande artisan)
     */
    public static function cleanExpired(): int
    {
        return self::where('last_activity', '<', now()->subHours(24))
            ->whereNull('user_id')
            ->delete();
    }
}
