<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizQuestionController extends Controller
{
    /**
     * Display a listing of questions
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 15);

        $questions = QuizQuestion::query()
            ->withCount('answers')
            ->when($search, function ($query, $search) {
                $query->where('question', 'like', "%{$search}%");
            })
            ->ordered()
            ->paginate($perPage);

        // Calculer les statistiques pour chaque question
        foreach ($questions as $question) {
            $question->correct_count = $question->correctAnswers()->count();
            $question->accuracy = $question->answers_count > 0
                ? round(($question->correct_count / $question->answers_count) * 100, 2)
                : 0;
        }

        return view('admin.quiz.questions.index', compact('questions', 'search'));
    }

    /**
     * Show the form for creating a new question
     */
    public function create()
    {
        return view('admin.quiz.questions.create');
    }

    /**
     * Store a newly created question
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question'       => 'required|string|max:1000',
            'option_a'       => 'required|string|max:255',
            'option_b'       => 'required|string|max:255',
            'option_c'       => 'required|string|max:255',
            'option_d'       => 'nullable|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'points'         => 'nullable|integer|min:1|max:100',
            'is_active'      => 'boolean',
            'order'          => 'nullable|integer|min:0',
        ]);

        // Vérifier que la réponse correcte a une option correspondante
        if ($validated['correct_answer'] === 'D' && empty($validated['option_d'])) {
            return back()
                ->withErrors(['correct_answer' => 'Si la réponse correcte est D, l\'option D doit être renseignée'])
                ->withInput();
        }

        $validated['points'] = $validated['points'] ?? 10;
        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? QuizQuestion::max('order') + 1;

        QuizQuestion::create($validated);

        return redirect()
            ->route('admin.quiz.questions.index')
            ->with('success', 'Question créée avec succès !');
    }

    /**
     * Display the specified question with statistics
     */
    public function show($id)
    {
        $question = QuizQuestion::withCount('answers', 'correctAnswers')
            ->findOrFail($id);

        // Statistiques détaillées
        $stats = [
            'total_answers' => $question->answers_count,
            'correct_answers' => $question->correct_answers_count,
            'wrong_answers' => $question->answers_count - $question->correct_answers_count,
            'accuracy_rate' => $question->answers_count > 0
                ? round(($question->correct_answers_count / $question->answers_count) * 100, 2)
                : 0,
        ];

        // Distribution des réponses
        $distribution = DB::table('quiz_answers')
            ->select('answer', DB::raw('count(*) as count'))
            ->where('quiz_question_id', $id)
            ->groupBy('answer')
            ->get()
            ->pluck('count', 'answer');

        // Réponses récentes
        $recentAnswers = $question->answers()
            ->with('user')
            ->latest('answered_at')
            ->limit(10)
            ->get();

        return view('admin.quiz.questions.show', compact('question', 'stats', 'distribution', 'recentAnswers'));
    }

    /**
     * Show the form for editing the specified question
     */
    public function edit($id)
    {
        $question = QuizQuestion::findOrFail($id);
        return view('admin.quiz.questions.edit', compact('question'));
    }

    /**
     * Update the specified question
     */
    public function update(Request $request, $id)
    {
        $question = QuizQuestion::findOrFail($id);

        $validated = $request->validate([
            'question'       => 'required|string|max:1000',
            'option_a'       => 'required|string|max:255',
            'option_b'       => 'required|string|max:255',
            'option_c'       => 'required|string|max:255',
            'option_d'       => 'nullable|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'points'         => 'nullable|integer|min:1|max:100',
            'is_active'      => 'boolean',
            'order'          => 'nullable|integer|min:0',
        ]);

        // Vérifier que la réponse correcte a une option correspondante
        if ($validated['correct_answer'] === 'D' && empty($validated['option_d'])) {
            return back()
                ->withErrors(['correct_answer' => 'Si la réponse correcte est D, l\'option D doit être renseignée'])
                ->withInput();
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['points'] = $validated['points'] ?? 10;

        $question->update($validated);

        return redirect()
            ->route('admin.quiz.questions.index')
            ->with('success', 'Question mise à jour avec succès !');
    }

    /**
     * Remove the specified question
     */
    public function destroy($id)
    {
        $question = QuizQuestion::findOrFail($id);

        // Vérifier si la question a des réponses
        if ($question->answers()->exists()) {
            return back()->with('error', 'Impossible de supprimer cette question car des utilisateurs y ont déjà répondu.');
        }

        $question->delete();

        return redirect()
            ->route('admin.quiz.questions.index')
            ->with('success', 'Question supprimée avec succès !');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->update(['is_active' => !$question->is_active]);

        return back()->with('success', 'Statut mis à jour avec succès !');
    }

    /**
     * Reorder questions
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:quiz_questions,id',
            'questions.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['questions'] as $item) {
            QuizQuestion::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true, 'message' => 'Ordre mis à jour avec succès !']);
    }
}
