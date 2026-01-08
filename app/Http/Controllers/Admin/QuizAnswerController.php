<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizAnswerController extends Controller
{
    /**
     * Display all quiz answers
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $questionFilter = $request->input('question_id');
        $correctFilter = $request->input('correct');
        $perPage = $request->input('per_page', 20);

        $answers = QuizAnswer::query()
            ->with(['user', 'question'])
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($questionFilter, function ($query, $questionId) {
                $query->where('quiz_question_id', $questionId);
            })
            ->when($correctFilter !== null, function ($query) use ($correctFilter) {
                $query->where('is_correct', $correctFilter);
            })
            ->latest('answered_at')
            ->paginate($perPage);

        // Statistiques globales
        $totalAnswers = QuizAnswer::count();
        $correctAnswers = QuizAnswer::where('is_correct', true)->count();
        $totalPoints = QuizAnswer::sum('points_won');
        $uniqueUsers = QuizAnswer::distinct('user_id')->count('user_id');

        $stats = [
            'total_answers' => $totalAnswers,
            'correct_answers' => $correctAnswers,
            'wrong_answers' => $totalAnswers - $correctAnswers,
            'accuracy_rate' => $totalAnswers > 0 ? round(($correctAnswers / $totalAnswers) * 100, 2) : 0,
            'total_points' => $totalPoints,
            'unique_users' => $uniqueUsers,
        ];

        $questions = QuizQuestion::all();

        return view('admin.quiz.answers.index', compact('answers', 'stats', 'questions', 'search', 'questionFilter', 'correctFilter'));
    }

    /**
     * Display answers for a specific question
     */
    public function show($questionId)
    {
        $question = QuizQuestion::withCount('answers', 'correctAnswers')->findOrFail($questionId);

        $answers = QuizAnswer::where('quiz_question_id', $questionId)
            ->with('user')
            ->latest('answered_at')
            ->paginate(20);

        // Distribution des réponses
        $distribution = DB::table('quiz_answers')
            ->select('answer', DB::raw('count(*) as count'), DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_count'))
            ->where('quiz_question_id', $questionId)
            ->groupBy('answer')
            ->get();

        $stats = [
            'total_answers' => $question->answers_count,
            'correct_answers' => $question->correct_answers_count,
            'wrong_answers' => $question->answers_count - $question->correct_answers_count,
            'accuracy_rate' => $question->answers_count > 0
                ? round(($question->correct_answers_count / $question->answers_count) * 100, 2)
                : 0,
        ];

        return view('admin.quiz.answers.show', compact('question', 'answers', 'distribution', 'stats'));
    }

    /**
     * Display quiz leaderboard
     */
    public function leaderboard(Request $request)
    {
        $perPage = $request->input('per_page', 50);
        $search = $request->input('search');

        $users = User::query()
            ->where('quiz_score', '>', 0)
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->withCount('quizAnswers')
            ->orderBy('quiz_score', 'desc')
            ->orderBy('quiz_answers_count', 'desc')
            ->paginate($perPage);

        // Ajouter le rang
        $users->getCollection()->transform(function ($user, $index) use ($users) {
            $user->rank = ($users->currentPage() - 1) * $users->perPage() + $index + 1;
            return $user;
        });

        // Statistiques globales
        $totalPlayers = User::where('quiz_score', '>', 0)->count();
        $totalPoints = User::sum('quiz_score');
        $avgScore = $totalPlayers > 0 ? round($totalPoints / $totalPlayers, 2) : 0;
        $topScore = User::max('quiz_score');

        $stats = [
            'total_players' => $totalPlayers,
            'total_points' => $totalPoints,
            'average_score' => $avgScore,
            'top_score' => $topScore,
        ];

        return view('admin.quiz.leaderboard', compact('users', 'stats', 'search'));
    }

    /**
     * Export quiz data to CSV
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'all'); // all, leaderboard, answers

        $filename = 'quiz_' . $type . '_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($type) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($type === 'leaderboard') {
                // Export du classement
                fputcsv($file, ['Rang', 'Nom', 'Téléphone', 'Score', 'Réponses', 'Correct', 'Incorrect', 'Taux de réussite']);

                $users = User::where('quiz_score', '>', 0)
                    ->withCount(['quizAnswers', 'quizAnswers as correct_answers' => function ($q) {
                        $q->where('is_correct', true);
                    }])
                    ->orderBy('quiz_score', 'desc')
                    ->orderBy('quiz_answers_count', 'desc')
                    ->get();

                foreach ($users as $index => $user) {
                    $incorrect = $user->quiz_answers_count - $user->correct_answers;
                    $accuracy = $user->quiz_answers_count > 0
                        ? round(($user->correct_answers / $user->quiz_answers_count) * 100, 2) . '%'
                        : '0%';

                    fputcsv($file, [
                        $index + 1,
                        $user->name,
                        $user->phone,
                        $user->quiz_score,
                        $user->quiz_answers_count,
                        $user->correct_answers,
                        $incorrect,
                        $accuracy,
                    ]);
                }
            } elseif ($type === 'answers') {
                // Export des réponses
                fputcsv($file, ['ID', 'Utilisateur', 'Téléphone', 'Question', 'Réponse', 'Correct', 'Points', 'Date']);

                $answers = QuizAnswer::with(['user', 'question'])
                    ->orderBy('answered_at', 'desc')
                    ->get();

                foreach ($answers as $answer) {
                    fputcsv($file, [
                        $answer->id,
                        $answer->user->name,
                        $answer->user->phone,
                        substr($answer->question->question, 0, 100),
                        $answer->answer,
                        $answer->is_correct ? 'Oui' : 'Non',
                        $answer->points_won,
                        $answer->answered_at->format('d/m/Y H:i'),
                    ]);
                }
            } else {
                // Export complet (statistiques par question)
                fputcsv($file, ['Question', 'Option correcte', 'Total réponses', 'Réponses correctes', 'Réponses incorrectes', 'Taux de réussite', 'Points distribués']);

                $questions = QuizQuestion::withCount(['answers', 'correctAnswers'])
                    ->with(['answers' => function ($q) {
                        $q->select('quiz_question_id', DB::raw('SUM(points_won) as total_points'))
                          ->groupBy('quiz_question_id');
                    }])
                    ->get();

                foreach ($questions as $question) {
                    $totalPoints = $question->answers()->sum('points_won');
                    $wrongAnswers = $question->answers_count - $question->correct_answers_count;
                    $accuracy = $question->answers_count > 0
                        ? round(($question->correct_answers_count / $question->answers_count) * 100, 2) . '%'
                        : '0%';

                    fputcsv($file, [
                        substr($question->question, 0, 100),
                        $question->correct_answer,
                        $question->answers_count,
                        $question->correct_answers_count,
                        $wrongAnswers,
                        $accuracy,
                        $totalPoints,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
