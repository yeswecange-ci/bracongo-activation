<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_question_id',
        'answer',
        'is_correct',
        'points_won',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_won' => 'integer',
        'answered_at' => 'datetime',
    ];

    /**
     * Get the user who answered
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the question that was answered
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    /**
     * Check if answer is correct and update accordingly
     */
    public function checkAnswer(): void
    {
        $question = $this->question;
        $this->is_correct = ($this->answer === $question->correct_answer);
        $this->points_won = $this->is_correct ? $question->points : 0;
        $this->save();

        // Update user's quiz score
        $this->user->increment('quiz_score', $this->points_won);
        $this->user->increment('quiz_answers_count');
    }

    /**
     * Scope for correct answers
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope for wrong answers
     */
    public function scopeWrong($query)
    {
        return $query->where('is_correct', false);
    }
}
