<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pronostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_id',
        'predicted_score_a',
        'predicted_score_b',
        'is_winner',
    ];

    protected $casts = [
        'is_winner' => 'boolean',
        'predicted_score_a' => 'integer',
        'predicted_score_b' => 'integer',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }

    /**
     * Vérifier si le pronostic est correct (score exact)
     */
    public function isCorrect(): bool
    {
        if (!$this->match || $this->match->status !== 'finished') {
            return false;
        }

        return $this->predicted_score_a === $this->match->score_a
            && $this->predicted_score_b === $this->match->score_b;
    }

    /**
     * Formater le pronostic (ex: "2 - 1")
     */
    public function getFormattedScoreAttribute(): string
    {
        return "{$this->predicted_score_a} - {$this->predicted_score_b}";
    }

    /**
     * Vérifier si le match peut encore recevoir des pronostics
     */
    public static function canBet(FootballMatch $match): bool
    {
        // Pas de pronostic si le match a commencé ou est terminé
        if (in_array($match->status, ['live', 'finished'])) {
            return false;
        }

        // Pas de pronostic si les pronostics sont désactivés
        if (!$match->pronostic_enabled) {
            return false;
        }

        // Pas de pronostic si le match est dans moins de 5 minutes
        if ($match->match_date->diffInMinutes(now(), false) < 5) {
            return false;
        }

        return true;
    }

    /**
     * Créer ou mettre à jour un pronostic
     */
    public static function createOrUpdate(User $user, FootballMatch $match, int $scoreA, int $scoreB): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $user->id,
                'match_id' => $match->id,
            ],
            [
                'predicted_score_a' => $scoreA,
                'predicted_score_b' => $scoreB,
            ]
        );
    }

    /**
     * Scope pour récupérer les pronostics gagnants
     */
    public function scopeWinners($query)
    {
        return $query->where('is_winner', true);
    }

    /**
     * Scope pour récupérer les pronostics d'un match
     */
    public function scopeForMatch($query, $matchId)
    {
        return $query->where('match_id', $matchId);
    }

    /**
     * Scope pour récupérer les pronostics d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
