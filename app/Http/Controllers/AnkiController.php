<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Progress;
use App\Services\QuizGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du mode Anki.
 * Questions infinies avec feedback immédiat et progression personnalisée.
 *
 * Progression (table `progress`) :
 * - success_count : bonnes réponses cumulées
 * - fail_count    : mauvaises réponses cumulées
 * - streak        : bonnes réponses consécutives (réinitialisé à 0 si erreur)
 * - Maîtrise      : streak >= 3
 *
 * Priorités :
 * - Items avec fail_count élevé = plus de chances
 * - Items maîtrisés = moins de chances (ou exclus)
 */
class AnkiController extends Controller
{
    /**
     * Lance une session Anki : affiche la première question.
     */
    public function show(Module $module)
    {
        $this->authorize($module);

        if ($module->items()->count() === 0) {
            return redirect()
                ->route('modules.show', $module)
                ->with('error', 'Vous devez d\'abord ajouter des items au module.');
        }

        // Initialiser la session Anki
        session([
            'anki_module_id' => $module->id,
        ]);

        return redirect()->route('anki.question', $module);
    }

    /**
     * Affiche la question courante (la première ou la suivante).
     */
    public function question(Module $module)
    {
        $this->authorize($module);

        if (!session()->has('anki_module_id') || session('anki_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucune session Anki en cours.');
        }

        // Tirer aléatoirement un type de question
        $types = QuizGenerator::getQuestionTypes();
        $questionType = $types[array_rand($types)];

        $question = QuizGenerator::generateQuestion($module, $questionType);

        if (isset($question['error'])) {
            return redirect()
                ->route('modules.show', $module)
                ->with('error', $question['error']);
        }

        // Récupérer la progression actuelle de cet item
        $progress = Progress::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'item_id' => $question['item_id'],
            ],
            [
                'success_count' => 0,
                'fail_count'    => 0,
                'streak'        => 0,
            ]
        );

        $question['progress'] = [
            'success_count' => $progress->success_count,
            'fail_count'    => $progress->fail_count,
            'streak'        => $progress->streak,
            'is_mastered'   => $progress->isMastered(),
        ];

        return view('quiz.anki.question', compact('module', 'question'));
    }

    /**
     * Traite la réponse, met à jour la progression et génère la prochaine question.
     */
    public function submit(Request $request, Module $module)
    {
        $this->authorize($module);

        if (!session()->has('anki_module_id') || session('anki_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucune session Anki en cours.');
        }

        // Valider la réponse
        $data = $request->validate([
            'item_id'   => 'required|integer',
            'answer'    => 'required|integer|min:0|max:3',
            'question'  => 'required|json', // Question encodée en JSON
        ]);

        // Décoder la question
        $question = json_decode($data['question'], true);

        if (!$question) {
            return redirect()
                ->route('anki.question', $module)
                ->with('error', 'Erreur lors du traitement de la réponse.');
        }

        // Valider la réponse
        $isCorrect = QuizGenerator::validateAnswer($question, $data['answer']);

        // Mettre à jour la progression
        $progress = Progress::where('user_id', Auth::id())
            ->where('item_id', $data['item_id'])
            ->firstOrFail();

        if ($isCorrect) {
            $progress->success_count++;
            $progress->streak++;
        } else {
            $progress->fail_count++;
            $progress->streak = 0; // Réinitialiser la série en cas d'erreur
        }

        $progress->last_seen = now();
        $progress->save();

        // Retourner le feedback
        return response()->json([
            'is_correct'  => $isCorrect,
            'correct_answer' => $question['correct_answer'],
            'user_answer' => $question['options'][$data['answer']],
            'streak'      => $progress->streak,
            'is_mastered' => $progress->isMastered(),
        ]);
    }

    /**
     * Quitter une session Anki.
     */
    public function quit(Module $module)
    {
        $this->authorize($module);

        session()->forget('anki_module_id');

        return redirect()
            ->route('modules.show', $module)
            ->with('success', 'Session Anki terminée.');
    }

    /**
     * Vérifie que l'utilisateur peut accéder au module.
     */
    private function authorize(Module $module): void
    {
        if (!$module->is_public && $module->owner_id !== Auth::id()) {
            abort(403, 'Ce module est privé.');
        }
    }
}
