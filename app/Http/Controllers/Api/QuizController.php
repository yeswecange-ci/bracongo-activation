<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Http\Request;
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
            'status'  => 'INSCRIT',
            'name'    => $user->name,
            'phone'   => $user->phone,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Endpoint: GET /api/can/quiz/questions/formatted
     * Récupérer la question formatée pour affichage WhatsApp
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
                'message'       => "🎉 *MERCI CHAMPION !*\n\n" .
                                   "Tu as déjà participé au Quiz Flash Ndembo City ! ✅\n\n" .
                                   "📍 Rendez-vous ce samedi dès 16h au Village Foot du Parc Maman Marthe (4ème Rue Limete Résidentiel) pour la remise de cadeaux ! 🎁\n\n" .
                                   "L'équipe Bracongo 🔥",
            ]);
        }

        // Formater la question pour WhatsApp
        $message = "*QUIZ FLASH NDEMBO CITY !* ⚽🔥\n\n";
        $message .= "Salut Champion !\n\n";
        $message .= "On teste tes connaissances aujourd'hui pour faire grimper ton score ! 📈\n\n";
        $message .= "*Question :* {$question->question} 🤔\n\n";
        $message .= "1️⃣ {$question->option_a}\n";
        $message .= "2️⃣ {$question->option_b}\n";
        $message .= "3️⃣ {$question->option_c}\n";

        if ($question->option_d) {
            $message .= "4️⃣ {$question->option_d}\n";
        }

        $message .= "\n_(Réponds simplement en tapant le chiffre 1, 2, 3" . ($question->option_d ? " ou 4" : "") . " ! ⚠️ Attention aux chiffres)_";

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
            'answered_at'   => $answer->answered_at->format('d/m/Y à H:i'),
            'message'       => "🎉 *MERCI CHAMPION !*\n\n" .
                               "Tu as déjà participé au Quiz Flash Ndembo City ! ✅\n\n" .
                               "📍 Rendez-vous ce samedi dès 16h au Village Foot du Parc Maman Marthe (4ème Rue Limete Résidentiel) pour la remise de cadeaux ! 🎁\n\n" .
                               "L'équipe Bracongo 🔥",
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
            return response()->json([
                'success' => false,
                'message' => "🎉 *MERCI CHAMPION !*\n\n" .
                             "Tu as déjà participé au Quiz Flash Ndembo City ! ✅\n\n" .
                             "📍 Rendez-vous ce samedi dès 16h au Village Foot du Parc Maman Marthe (4ème Rue Limete Résidentiel) pour la remise de cadeaux ! 🎁\n\n" .
                             "L'équipe Bracongo 🔥",
            ], 400);
        }

        // Convertir la réponse numérique en lettre pour comparaison
        $answerLetter = $this->convertNumberToLetter($validated['answer']);

        // Vérifier si la réponse est correcte (pour les stats internes)
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

        // Mettre à jour le score de l'utilisateur (pour les stats internes)
        $user->increment('quiz_score', $pointsWon);
        $user->increment('quiz_answers_count');

        // Message de confirmation uniforme (ne révèle pas si c'est correct)
        $message = "*Réponse enregistrée !* ✅\n\n" .
                   "Merci de ta participation !\n\n" .
                   "On se donne rendez-vous ce samedi dès 16h au Village Foot du Parc Maman Marthe (4ème Rue Limete Résidentiel) pour une remise de cadeaux exceptionnelle à nos premiers gagnants ! 🥳📱🎁\n\n" .
                   "Continue de participer et reste au top du classement ! 🚀\n\n" .
                   "L'équipe Bracongo.";

        Log::info('Quiz answer saved', [
            'user_id'     => $user->id,
            'question_id' => $question->id,
            'answer'      => $validated['answer'],
            'is_correct'  => $isCorrect,
            'points_won'  => $pointsWon,
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
            'answer'  => [
                'id'         => $answer->id,
                'question'   => $question->question,
                'your_answer'=> $validated['answer'],
            ],
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    /**
     * Endpoint: GET /api/can/quiz/leaderboard
     * Récupérer le classement des joueurs (pour usage interne/admin)
     */
    public function getLeaderboard(Request $request)
    {
        $limit = $request->input('limit', 50);

        $topUsers = User::where('is_active', true)
            ->where('quiz_score', '>', 0)
            ->orderBy('quiz_score', 'desc')
            ->orderBy('quiz_answers_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'phone', 'quiz_score', 'quiz_answers_count']);

        if ($topUsers->isEmpty()) {
            return response()->json([
                'success'         => true,
                'has_leaderboard' => false,
                'message'         => 'Aucun participant pour le moment.',
            ]);
        }

        return response()->json([
            'success'         => true,
            'has_leaderboard' => true,
            'count'           => $topUsers->count(),
            'leaderboard'     => $topUsers->map(function ($user, $index) {
                return [
                    'position' => $index + 1,
                    'name'     => $user->name,
                    'phone'    => $user->phone,
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