<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    use HasFactory;

    protected $table = 'matches'; // Important: spécifier le nom de la table

    protected $fillable = [
        'team_a',
        'team_b',
        'match_date',
        'score_a',
        'score_b',
        'status',
        'pronostic_enabled',
        'prize_id',
        'winners_calculated',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'pronostic_enabled' => 'boolean',
        'winners_calculated' => 'boolean',
    ];

    // Relations
    public function pronostics()
    {
        return $this->hasMany(Pronostic::class, 'match_id');
    }

    public function prizeWinners()
    {
        return $this->hasMany(PrizeWinner::class, 'match_id');
    }

    public function prize()
    {
        return $this->belongsTo(Prize::class);
    }
}
