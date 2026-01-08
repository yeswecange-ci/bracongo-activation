<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $fillable = [
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'points',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get all answers for this question
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }

    /**
     * Get correct answers for this question
     */
    public function correctAnswers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class)->where('is_correct', true);
    }

    /**
     * Get statistics for this question
     */
    public function getStatisticsAttribute(): array
    {
        $total = $this->answers()->count();
        $correct = $this->correctAnswers()->count();
        $accuracy = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

        return [
            'total_answers' => $total,
            'correct_answers' => $correct,
            'wrong_answers' => $total - $correct,
            'accuracy_rate' => $accuracy,
        ];
    }

    /**
     * Scope for active questions only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering questions
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('created_at', 'asc');
    }
}
