<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    /**
     * Endpoint: POST /api/can/quiz/check-user
     * Vérifier si l'utilisateur existe
     */
    public function checkUser(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);
        $user  = User::where('phone', $phone)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'NOT_FOUND',
                'message' => 'Utilisateur non trouvé. Veuillez vous inscrire d\'abord.',
            ]);
        }

        if (! $user->is_active || $user->registration_status === 'STOP') {
            return response()->json([
                'status'  => 'STOP',
                'name'    => $user->name,
                'phone'   => $user->phone,
                'message' => 'Utilisateur désactivé.',
            ]);
        }

        return response()->json([
            'status'       => 'INSCRIT',
            'name'         => $user->name,
            'phone'        => $user->phone,
            'user_id'      => $user->id,
            'quiz_score'   => $user->quiz_score ?? 0,
            'quiz_answers' => $user->quiz_answers_count ?? 0,
        ]);
    }

    /**
     * Endpoint: GET /api/can/quiz/questions
     * Récupérer les questions actives non répondues par l'utilisateur
     */
    public function getQuestions(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:10',
        ]);

        $phone = $this->formatPhone($validated['phone']);
        $user  = User::where('phone', $phone)->where('is_active', true)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.',
            ], 404);
        }

        $limit = $validated['limit'] ?? 3;

        // Récupérer les IDs des questions déjà répondues
        $answeredQuestionIds = QuizAnswer::where('user_id', $user->id)
            ->pluck('quiz_question_id')
            ->toArray();

        // Récupérer les questions actives non répondues
        $questions = QuizQuestion::active()
            ->whereNotIn('id', $answeredQuestionIds)
            ->ordered()
            ->limit($limit)
            ->get();

        if ($questions->isEmpty()) {
            // L'utilisateur a répondu à toutes les questions
            return response()->json([
                'success'       => true,
                'has_questions' => false,
                'all_answered'  => true,
                'message'       => "🎉 *BRAVO !*\n\n" .
                                   "Tu as déjà répondu à toutes les questions du quiz !\n\n" .
                                   "📊 Ton score : {$user->quiz_score} points\n" .
                                   "✅ Questions répondues : {$user->quiz_answers_count}\n\n" .
                                   "🏆 Continue de parier sur les matchs pour gagner plus de points !",
            ]);
        }

        // Formater les questions pour WhatsApp
        $message = "🎯 *QUIZ CAN 2025*\n\n";
        $message .= "Réponds correctement et gagne 10 points par bonne réponse !\n\n";
        $message .= "📊 Ton score actuel : {$user->quiz_score} points\n\n";

        $formattedQuestions = $questions->map(function ($question, $index) {
            return [
                'id'             => $question->id,
                'number'         => $index + 1,
                'question'       => $question->question,
                'option_a'       => $question->option_a,
                'option_b'       => $question->option_b,
                'option_c'       => $question->option_c,
                'option_d'       => $question->option_d,
                'correct_answer' => $question->correct_answer,
                'points'         => $question->points,
            ];
        });

        return response()->json([
            'success'       => true,
            'has_questions' => true,
            'all_answered'  => false,
            'count'         => $questions->count(),
            'total_active'  => QuizQuestion::active()->count(),
            'user_score'    => $user->quiz_score,
            'questions'     => $formattedQuestions,
            'message'       => $message,
        ]);
    }

    /**
     * Endpoint: GET /api/can/quiz/questions/formatted
     * Récupérer les questions formatées pour affichage WhatsApp
     */
    public function getQuestionsFormatted(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);
        $user  = User::where('phone', $phone)->where('is_active', true)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.',
            ], 404);
        }

        // Récupérer les IDs des questions déjà répondues
        $answeredQuestionIds = QuizAnswer::where('user_id', $user->id)
            ->pluck('quiz_question_id')
            ->toArray();

        // Récupérer une seule question non répondue
        $question = QuizQuestion::active()
            ->whereNotIn('id', $answeredQuestionIds)
            ->ordered()
            ->first();

        if (! $question) {
            return response()->json([
                'success'       => true,
                'has_questions' => false,
                'all_answered'  => true,
                'message'       => "🎉 *BRAVO !*\n\n" .
                                   "Tu as déjà répondu à toutes les questions du quiz !\n\n" .
                                   "📊 Ton score : {$user->quiz_score} points\n" .
                                   "✅ Questions répondues : {$user->quiz_answers_count}\n\n" .
                                   "🏆 Reviens bientôt pour de nouvelles questions !",
            ]);
        }

        // Formater la question pour WhatsApp
        $message = "🎯 *QUESTION QUIZ*\n\n";
        $message .= "📊 Ton score : {$user->quiz_score} points\n\n";
        $message .= "❓ {$question->question}\n\n";
        $message .= "1. {$question->option_a}\n";
        $message .= "2. {$question->option_b}\n";
        $message .= "3. {$question->option_c}\n";

        if ($question->option_d) {
            $message .= "4. {$question->option_d}\n";
        }

        $message .= "\n💡 Réponds par 1, 2, 3" . ($question->option_d ? " ou 4" : "") . " !";

        return response()->json([
            'success'       => true,
            'has_questions' => true,
            'all_answered'  => false,
            'question'      => [
                'id'       => $question->id,
                'question' => $question->question,
                'options'  => [
                    '1' => $question->option_a,
                    '2' => $question->option_b,
                    '3' => $question->option_c,
                    '4' => $question->option_d,
                ],
                'points'   => $question->points,
            ],
            'message'       => $message,
        ]);
    }

    /**
     * Endpoint: POST /api/can/quiz/check-answer
     * Vérifier si l'utilisateur a déjà répondu à une question
     */
    public function checkAnswer(Request $request)
    {
        $validated = $request->validate([
            'phone'       => 'required|string',
            'question_id' => 'required|integer|exists:quiz_questions,id',
        ]);

        $phone = $this->formatPhone($validated['phone']);
        $user  = User::where('phone', $phone)->where('is_active', true)->first();

        if (! $user) {
            return response()->json([
                'has_answered' => false,
                'message'      => 'Utilisateur non trouvé',
            ]);
        }

        $answer = QuizAnswer::where('user_id', $user->id)
            ->where('quiz_question_id', $validated['question_id'])
            ->first();

        if (! $answer) {
            return response()->json([
                'has_answered' => false,
                'message'      => 'Aucune réponse trouvée',
            ]);
        }

        return response()->json([
            'has_answered'  => true,
            'answer'        => $answer->answer,
            'is_correct'    => $answer->is_correct,
            'points_won'    => $answer->points_won,
            'answered_at'   => $answer->answered_at->format('d/m/Y à H:i'),
            'message'       => 'Réponse déjà enregistrée',
        ]);
    }

    /**
     * Endpoint: POST /api/can/quiz/answer
     * Enregistrer la réponse d'un utilisateur
     */
    public function saveAnswer(Request $request)
    {
        Log::info('=== DÉBUT saveAnswer ===', [
            'all_data' => $request->all(),
        ]);

        $validated = $request->validate([
            'phone'       => 'required|string',
            'question_id' => 'required|integer|exists:quiz_questions,id',
            'answer'      => 'required|in:1,2,3,4',
        ]);

        $phone = $this->formatPhone($validated['phone']);
        $user  = User::where('phone', $phone)->where('is_active', true)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé. Veuillez vous inscrire d\'abord.',
            ], 404);
        }

        $question = QuizQuestion::find($validated['question_id']);

        if (! $question->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cette question n\'est plus active.',
            ], 400);
        }

        // Vérifier si l'utilisateur a déjà répondu
        $existingAnswer = QuizAnswer::where('user_id', $user->id)
            ->where('quiz_question_id', $question->id)
            ->first();

        if ($existingAnswer) {
            $resultText = $existingAnswer->is_correct ? "✅ Bonne réponse" : "❌ Mauvaise réponse";

            return response()->json([
                'success' => false,
                'message' => "🚫 *TU AS DÉJÀ RÉPONDU*\n\n" .
                             "❓ {$question->question}\n\n" .
                             "📊 Ta réponse : {$existingAnswer->answer}\n" .
                             "{$resultText} ({$existingAnswer->points_won} points)\n" .
                             "📅 Répondu le : " . $existingAnswer->answered_at->format('d/m/Y à H:i') . "\n\n" .
                             "❌ Tu ne peux répondre qu'une seule fois !",
            ], 400);
        }

        // Convertir la réponse numérique en lettre pour comparaison
        $answerLetter = $this->convertNumberToLetter($validated['answer']);

        // Vérifier si la réponse est correcte
        $isCorrect = ($answerLetter === $question->correct_answer);
        $pointsWon = $isCorrect ? $question->points : 0;

        // Enregistrer la réponse
        $answer = QuizAnswer::create([
            'user_id'           => $user->id,
            'quiz_question_id'  => $question->id,
            'answer'            => $validated['answer'],
            'is_correct'        => $isCorrect,
            'points_won'        => $pointsWon,
            'answered_at'       => now(),
        ]);

        // Mettre à jour le score de l'utilisateur
        $user->increment('quiz_score', $pointsWon);
        $user->increment('quiz_answers_count');

        // Préparer le message de réponse
        if ($isCorrect) {
            $message = "✅ *BRAVO !*\n\n" .
                       "Ta réponse est correcte !\n\n" .
                       "🎯 Points gagnés : +{$pointsWon} points\n" .
                       "📊 Ton score total : {$user->quiz_score} points\n\n" .
                       "🔥 Continue comme ça !";
        } else {
            $message = "❌ *DOMMAGE !*\n\n" .
                       "La bonne réponse était : {$question->correct_answer}\n\n" .
                       "📊 Ton score : {$user->quiz_score} points\n\n" .
                       "💪 Ne te décourage pas, continue !";
        }

        Log::info('Quiz answer saved', [
            'user_id'     => $user->id,
            'question_id' => $question->id,
            'answer'      => $validated['answer'],
            'is_correct'  => $isCorrect,
            'points_won'  => $pointsWon,
        ]);

        return response()->json([
            'success'    => true,
            'is_correct' => $isCorrect,
            'points_won' => $pointsWon,
            'user_score' => $user->quiz_score,
            'message'    => $message,
            'answer'     => [
                'id'            => $answer->id,
                'question'      => $question->question,
                'your_answer'   => $validated['answer'],
                'correct_answer'=> $question->correct_answer,
                'is_correct'    => $isCorrect,
                'points'        => $pointsWon,
            ],
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    /**
     * Endpoint: POST /api/can/quiz/history
     * Récupérer l'historique des réponses de l'utilisateur
     */
    public function getHistory(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->formatPhone($validated['phone']);
        $user  = User::where('phone', $phone)->where('is_active', true)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.',
            ], 404);
        }

        $answers = QuizAnswer::where('user_id', $user->id)
            ->with('question')
            ->orderBy('answered_at', 'desc')
            ->get();

        if ($answers->isEmpty()) {
            return response()->json([
                'success'     => true,
                'has_answers' => false,
                'message'     => "📊 *TON HISTORIQUE*\n\n" .
                                 "Tu n'as pas encore répondu au quiz.\n\n" .
                                 "🎯 Commence maintenant et gagne des points !",
            ]);
        }

        // Construire le message d'historique
        $message = "📊 *TON HISTORIQUE QUIZ*\n\n";
        $message .= "🏆 Score total : {$user->quiz_score} points\n";
        $message .= "✅ Réponses correctes : " . $answers->where('is_correct', true)->count() . "\n";
        $message .= "❌ Réponses incorrectes : " . $answers->where('is_correct', false)->count() . "\n\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n\n";

        foreach ($answers as $index => $answer) {
            $question = $answer->question;
            $number = $index + 1;
            $icon = $answer->is_correct ? "✅" : "❌";

            $message .= "{$number}. {$icon} {$question->question}\n";
            $message .= "   Ta réponse : {$answer->answer}\n";

            if (!$answer->is_correct) {
                $message .= "   Bonne réponse : {$question->correct_answer}\n";
            }

            $message .= "   Points : {$answer->points_won}\n";
            $message .= "   Date : " . $answer->answered_at->format('d/m à H:i') . "\n\n";
        }

        return response()->json([
            'success'          => true,
            'has_answers'      => true,
            'total_score'      => $user->quiz_score,
            'total_answers'    => $answers->count(),
            'correct_answers'  => $answers->where('is_correct', true)->count(),
            'wrong_answers'    => $answers->where('is_correct', false)->count(),
            'message'          => $message,
            'answers'          => $answers->map(function ($answer) {
                return [
                    'question'       => $answer->question->question,
                    'your_answer'    => $answer->answer,
                    'correct_answer' => $answer->question->correct_answer,
                    'is_correct'     => $answer->is_correct,
                    'points_won'     => $answer->points_won,
                    'answered_at'    => $answer->answered_at->format('d/m/Y à H:i'),
                ];
            }),
        ]);
    }

    /**
     * Endpoint: GET /api/can/quiz/leaderboard
     * Récupérer le classement des joueurs
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->input('limit', 10);

        $topUsers = User::where('is_active', true)
            ->where('quiz_score', '>', 0)
            ->orderBy('quiz_score', 'desc')
            ->orderBy('quiz_answers_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'quiz_score', 'quiz_answers_count']);

        if ($topUsers->isEmpty()) {
            return response()->json([
                'success'        => true,
                'has_leaderboard'=> false,
                'message'        => "🏆 *CLASSEMENT QUIZ*\n\n" .
                                    "Le classement est vide pour le moment.\n\n" .
                                    "🎯 Sois le premier à jouer !",
            ]);
        }

        // Construire le message du classement
        $message = "🏆 *CLASSEMENT QUIZ CAN 2025*\n\n";
        $message .= "🔝 Top {$topUsers->count()} joueurs\n\n";

        foreach ($topUsers as $index => $user) {
            $position = $index + 1;
            $medal = match($position) {
                1 => "🥇",
                2 => "🥈",
                3 => "🥉",
                default => "{$position}.",
            };

            $message .= "{$medal} {$user->name}\n";
            $message .= "   📊 {$user->quiz_score} points ({$user->quiz_answers_count} réponses)\n\n";
        }

        // Position de l'utilisateur actuel si disponible
        if ($request->has('phone')) {
            $phone = $this->formatPhone($request->input('phone'));
            $currentUser = User::where('phone', $phone)->first();

            if ($currentUser && $currentUser->quiz_score > 0) {
                $userPosition = User::where('is_active', true)
                    ->where('quiz_score', '>', $currentUser->quiz_score)
                    ->count() + 1;

                $message .= "━━━━━━━━━━━━━━━━━━━━\n";
                $message .= "📍 Ta position : #{$userPosition}\n";
                $message .= "📊 Ton score : {$currentUser->quiz_score} points\n";
            }
        }

        return response()->json([
            'success'         => true,
            'has_leaderboard' => true,
            'count'           => $topUsers->count(),
            'message'         => $message,
            'leaderboard'     => $topUsers->map(function ($user, $index) {
                return [
                    'position' => $index + 1,
                    'name'     => $user->name,
                    'score'    => $user->quiz_score,
                    'answers'  => $user->quiz_answers_count,
                ];
            }),
        ]);
    }

    /**
     * Convertir un chiffre (1,2,3,4) en lettre (A,B,C,D)
     */
    private function convertNumberToLetter(string $number): string
    {
        return match($number) {
            '1' => 'A',
            '2' => 'B',
            '3' => 'C',
            '4' => 'D',
            default => $number,
        };
    }

    /**
     * Formater le numéro de téléphone
     */
    private function formatPhone(string $phone): string
    {
        $phone = str_replace('whatsapp:', '', $phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (! str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }
}
