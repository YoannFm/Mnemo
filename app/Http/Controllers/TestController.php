<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Score;
use App\Services\QuizGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du mode Test.
 * Gère les sessions de test avec un nombre fixe de questions et score final.
 *
 * Flux :
 * 1. show() → Formulaire de choix du nombre de questions
 * 2. start() → Génère la première question, démarre la session test
 * 3. question() → Affiche la question courante
 * 4. submit() → Valide la réponse, génère la suivante ou fin du test
 * 5. result() → Affiche le score final et l'enregistre en base
 */
class TestController extends Controller
{
    /**
     * Affiche le formulaire de choix du nombre de questions.
     */
    public function show(Module $module)
    {
        // Vérifier que l'utilisateur peut accéder au module
        $this->authorize($module);

        // Vérifier qu'il y a au moins des items
        if ($module->items()->count() === 0) {
            return redirect()
                ->route('modules.show', $module)
                ->with('error', 'Vous devez d\'abord ajouter des items au module.');
        }

        return view('quiz.test.start', compact('module'));
    }

    /**
     * Démarre une session de test : génère la première question.
     */
    public function start(Request $request, Module $module)
    {
        $this->authorize($module);

        // Valider le nombre de questions
        $questionCount = $request->validate([
            'question_count' => 'required|integer|min:1|max:100',
        ])['question_count'];

        // Limiter au nombre d'items du module
        $itemCount = $module->items()->count();
        $questionCount = min($questionCount, $itemCount);

        // Initialiser la session du test
        session([
            'test_module_id'      => $module->id,
            'test_question_count' => $questionCount,
            'test_current'        => 0,         // Question actuelle (0-indexed)
            'test_score'          => 0,         // Score cumulé
            'test_answers'        => [],        // [question_index => {item_id, question_type, user_answer, correct}]
            'test_questions'      => [],        // Cache des questions générées
        ]);

        return redirect()->route('test.question', $module);
    }

    /**
     * Affiche la question courante du test.
     */
    public function question(Module $module)
    {
        $this->authorize($module);

        // Vérifier que le test est en cours
        if (!session()->has('test_module_id') || session('test_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucun test en cours.');
        }

        $currentIndex = session('test_current');
        $questionCount = session('test_question_count');

        // Si on a répondu à toutes les questions, aller au résultat
        if ($currentIndex >= $questionCount) {
            return redirect()->route('test.result', $module);
        }

        // Générer la question si elle n'existe pas en cache
        $questions = session('test_questions', []);

        if (!isset($questions[$currentIndex])) {
            // Tirer aléatoirement un type de question (Q1-Q4)
            $types = QuizGenerator::getQuestionTypes();
            $questionType = $types[array_rand($types)];

            $question = QuizGenerator::generateQuestion($module, $questionType);

            if (isset($question['error'])) {
                return redirect()
                    ->route('modules.show', $module)
                    ->with('error', $question['error']);
            }

            $questions[$currentIndex] = $question;
            session(['test_questions' => $questions]);
        }

        $question = $questions[$currentIndex];

        // Ajouter l'info de progression
        $question['current'] = $currentIndex + 1;
        $question['total']   = $questionCount;

        return view('quiz.test.question', compact('module', 'question', 'currentIndex'));
    }

    /**
     * Traite la réponse soumise, génère la question suivante ou va au résultat.
     */
    public function submit(Request $request, Module $module)
    {
        $this->authorize($module);

        if (!session()->has('test_module_id') || session('test_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucun test en cours.');
        }

        // Valider la réponse
        $validated = $request->validate([
            'answer'          => 'required|integer|min:0|max:3',
            'elapsed_seconds' => 'nullable|integer|min:0|max:3600',
        ]);
        $userAnswer = $validated['answer'];

        $currentIndex = session('test_current');
        $questions = session('test_questions');
        $question = $questions[$currentIndex];

        // Valider la réponse
        $isCorrect = QuizGenerator::validateAnswer($question, $userAnswer);

        // Enregistrer la réponse
        $answers = session('test_answers', []);
        $answers[$currentIndex] = [
            'item_id'         => $question['item_id'],
            'question_type'   => $question['question_type'],
            'user_answer'     => $question['options'][$userAnswer],
            'correct_answer'  => $question['correct_answer'],
            'is_correct'      => $isCorrect,
            'elapsed_seconds' => (int) ($validated['elapsed_seconds'] ?? 0),
        ];

        // Incrémenter le score si correct
        $score = session('test_score', 0);
        if ($isCorrect) {
            $score++;
        }

        // Sauvegarder la progression
        session([
            'test_answers' => $answers,
            'test_score'   => $score,
            'test_current' => $currentIndex + 1,
        ]);

        // Aller à la prochaine question ou au résultat
        return redirect()->route('test.question', $module);
    }

    /**
     * Affiche le résultat final du test et enregistre le score en base.
     */
    public function result(Module $module)
    {
        $this->authorize($module);

        if (!session()->has('test_module_id') || session('test_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucun test en cours.');
        }

        $score = session('test_score', 0);
        $total = session('test_question_count', 0);
        $answers = session('test_answers', []);

        // Enregistrer le score en base
        Score::create([
            'user_id'   => Auth::id(),
            'module_id' => $module->id,
            'score'     => $score,
            'total'     => $total,
        ]);

        // Nettoyer la session du test
        session()->forget(['test_module_id', 'test_question_count', 'test_current', 'test_score', 'test_answers', 'test_questions']);

        $percentage = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        return view('quiz.test.result', compact('module', 'score', 'total', 'percentage', 'answers'));
    }

    /**
     * Vérifie que l'utilisateur peut accéder au module.
     * Un utilisateur ne peut tester que les modules publics ou dont il est propriétaire.
     */
    private function authorize(Module $module): void
    {
        $user = Auth::user();
        $role = $user ? $user->role : null;

        if (!$module->is_public && $module->owner_id !== $user?->id) {
            abort(403, 'Ce module est prive.');
        }

        if ($module->owner_id === $user?->id) {
            if ($role && !$role->can_test_own && !$user->is_admin) {
                abort(403, 'Vous n\'avez pas la permission de tester vos modules.');
            }
        } else {
            if ($role && !$role->can_test_public && !$user->is_admin) {
                abort(403, 'Vous n\'avez pas la permission de tester les modules publics.');
            }
        }
    }
}
