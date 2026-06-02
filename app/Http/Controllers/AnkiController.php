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

        // --- Priorisation des items ratés (ANKI-3) ---
        // Au lieu d'un tirage purement aléatoire, on pondère les items
        // selon la progression de l'utilisateur : les items ratés reviennent plus souvent.
        $allItems = $module->items()->get();

        // Récupérer toutes les progressions de l'utilisateur pour ce module
        $progressMap = Progress::where('user_id', Auth::id())
            ->whereIn('item_id', $allItems->pluck('id'))
            ->get()
            ->keyBy('item_id');

        // Construire une liste pondérée : items ratés et items dus (SM-2) apparaissent plus souvent
        $weightedPool = [];
        foreach ($allItems as $item) {
            $prog = $progressMap->get($item->id);
            if (!$prog) {
                // Jamais vu : poids élevé (priorité haute)
                $weight = 5;
            } elseif ($prog->isMastered() && !$prog->isDueForReview()) {
                // Maîtrisé et pas encore dû : poids très faible
                $weight = 1;
            } elseif ($prog->isDueForReview() && !$prog->isMastered()) {
                // Dû pour révision et non maîtrisé : poids très élevé (SM-2 priorité)
                $weight = max(5, $prog->fail_count - $prog->success_count + 5);
            } elseif ($prog->isDueForReview()) {
                // Dû pour révision (même si maîtrisé) : poids moyen
                $weight = 3;
            } else {
                // En cours mais pas encore dû : poids selon les erreurs
                $weight = max(1, $prog->fail_count - $prog->success_count + 3);
            }
            for ($i = 0; $i < $weight; $i++) {
                $weightedPool[] = $item;
            }
        }

        // Tirer l'item cible selon les poids calculés
        $targetItem = $weightedPool[array_rand($weightedPool)];
        // --- Fin priorisation ---

        $question = QuizGenerator::generateQuestion($module, $questionType, $targetItem);

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
            return response()->json(['error' => 'Aucune session Anki en cours.'], 403);
        }

        try {
            // Valider la réponse
            // Laravel parse automatiquement le body JSON quand Content-Type: application/json
            // 'question' arrive comme array, pas comme string JSON
            $data = $request->validate([
                'item_id'  => 'required|integer',
                'answer'   => 'required|integer|min:0|max:3',
                'question' => 'required|array',
            ]);

            // La question est déjà un array (décodé par Laravel)
            $question = $data['question'];

            if (empty($question)) {
                return response()->json(['error' => 'Erreur lors du traitement de la réponse.'], 422);
            }

            // Valider la réponse
            $isCorrect = QuizGenerator::validateAnswer($question, $data['answer']);

            // Récupérer ou créer la progression (firstOrCreate évite un 404 si la session a été perdue)
            $progress = Progress::firstOrCreate(
                ['user_id' => Auth::id(), 'item_id' => $data['item_id']],
                ['success_count' => 0, 'fail_count' => 0, 'streak' => 0, 'easiness_factor' => 2.5, 'interval_days' => 1]
            );

            if ($isCorrect) {
                $progress->success_count++;
                $progress->streak++;
            } else {
                $progress->fail_count++;
                $progress->streak = 0; // Réinitialiser la série en cas d'erreur
            }

            $progress->last_seen = now();

            // Appliquer l'algorithme SM-2 (qualité 5 si correct, 1 si incorrect)
            $quality = $isCorrect ? 5 : 1;
            $progress->applySM2($quality);

            $progress->save();

            // Retourner le feedback
            return response()->json([
                'is_correct'  => $isCorrect,
                'correct_answer' => $question['correct_answer'],
                'user_answer' => $question['options'][$data['answer']],
                'streak'      => $progress->streak,
                'is_mastered' => $progress->isMastered(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Anki submit error: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'module_id' => $module->id,
                'request_data' => $request->all(),
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
