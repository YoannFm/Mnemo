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

        return view('quiz.anki.setup', compact('module'));
    }

    public function start(Request $request, Module $module)
    {
        $this->authorize($module);

        $mode = $request->input('mode', 'random');

        session([
            'anki_module_id'      => $module->id,
            'anki_mode'           => $mode,
            'anki_session_correct' => 0,
            'anki_session_wrong'   => 0,
            'anki_session_streak'  => 0,
        ]);

        // Si mode spécifique (pas random), activer le mode apprentissage
        if ($mode !== 'random') {
            $allItemIds = $module->items()->pluck('id')->toArray();
            shuffle($allItemIds);
            session([
                'anki_learn_mode'      => true,
                'anki_learn_remaining' => $allItemIds,
                'anki_learn_total'     => count($allItemIds),
            ]);
        } else {
            session()->forget(['anki_learn_mode', 'anki_learn_remaining', 'anki_learn_total']);
        }

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

        $mode = session('anki_mode', 'random');
        $learnMode = session('anki_learn_mode', false);

        $modeMap = [
            'photo_to_name_fr'    => ['Q1'],
            'photo_to_name_alt'    => ['Q8'],
            'photo_to_function'   => ['Q2'],
            'function_to_photo'   => ['Q3'],
            'function_to_name_fr' => ['Q6'],
            'function_to_name_alt' => ['Q11'],
            'name_fr_to_name_alt'  => ['Q4'],
            'name_fr_to_photo'    => ['Q9'],
            'name_fr_to_function' => ['Q10'],
            'name_alt_to_photo'    => ['Q5'],
            'name_alt_to_function' => ['Q11'],
            'name_alt_to_name_fr'  => ['Q7'],
            'audio_to_name_fr'    => ['Q13'],
            'audio_to_name_alt'   => ['Q14'],
            'name_fr_to_audio'    => ['Q15'],
            'name_alt_to_audio'   => ['Q16'],
        ];

        if ($mode === 'random' || !isset($modeMap[$mode])) {
            $mediaAnswerTypes = ['Q3', 'Q5', 'Q9', 'Q15', 'Q16'];
            $types = array_values(array_diff(QuizGenerator::getQuestionTypes(), $mediaAnswerTypes));
        } else {
            $types = $modeMap[$mode];
        }

        $questionType = $types[array_rand($types)];

        $allItems = $module->items()->get();

        // Filtrer les items qui possèdent le champ d'entrée requis par le type choisi
        $fieldQuestion = \App\Services\QuizGenerator::getFieldQuestion($questionType);
        $eligibleItems = $fieldQuestion
            ? $allItems->filter(fn($i) => !empty($i->{$fieldQuestion}))
            : $allItems;
        if ($eligibleItems->isEmpty()) {
            $eligibleItems = $allItems;
        }

        if ($learnMode) {
            $remaining = session('anki_learn_remaining', []);
            $learnTotal = session('anki_learn_total', 0);

            if (empty($remaining)) {
                session()->forget(['anki_module_id', 'anki_mode', 'anki_learn_mode', 'anki_learn_remaining', 'anki_learn_total', 'anki_session_correct', 'anki_session_wrong', 'anki_session_streak']);
                return redirect()->route('modules.show', $module)->with('success', 'Bravo ! Vous avez maitrisé tous les items de ce module.');
            }

            $eligibleRemaining = array_values(array_filter($remaining, fn($id) => $eligibleItems->contains('id', $id)));
            if (empty($eligibleRemaining)) {
                $eligibleRemaining = $remaining;
            }
            $targetItemId = $eligibleRemaining[array_rand($eligibleRemaining)];
            $targetItem = $allItems->firstWhere('id', $targetItemId);
        } else {
            // Weighted pool (SM-2)
            $progressMap = Progress::where('user_id', Auth::id())
                ->whereIn('item_id', $allItems->pluck('id'))
                ->get()
                ->keyBy('item_id');

            $weightedPool = [];
            foreach ($eligibleItems as $item) {
                $prog = $progressMap->get($item->id);
                if (!$prog) {
                    $weight = 5;
                } elseif ($prog->isMastered() && !$prog->isDueForReview()) {
                    $weight = 1;
                } elseif ($prog->isDueForReview() && !$prog->isMastered()) {
                    $weight = max(5, $prog->fail_count - $prog->success_count + 5);
                } elseif ($prog->isDueForReview()) {
                    $weight = 3;
                } else {
                    $weight = max(1, $prog->fail_count - $prog->success_count + 3);
                }
                for ($i = 0; $i < $weight; $i++) {
                    $weightedPool[] = $item;
                }
            }

            $targetItem = $weightedPool[array_rand($weightedPool)];
        }

        $question = QuizGenerator::generateQuestion($module, $questionType, $targetItem, $types);

        if (isset($question['error'])) {
            return redirect()->route('modules.show', $module)->with('error', $question['error']);
        }

        // Stats de session (globales, pas par item)
        $question['session'] = [
            'correct' => session('anki_session_correct', 0),
            'wrong'   => session('anki_session_wrong', 0),
            'streak'  => session('anki_session_streak', 0),
        ];

        if ($learnMode) {
            $question['learn_remaining'] = count($remaining);
            $question['learn_total']     = $learnTotal;
        }

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
            $data = $request->validate([
                'item_id'  => 'required|integer',
                'knows'    => 'required|boolean',
                'question' => 'required|array',
            ]);

            $question  = $data['question'];
            $isCorrect = (bool) $data['knows'];

            $progress = Progress::firstOrCreate(
                ['user_id' => Auth::id(), 'item_id' => $data['item_id']],
                ['success_count' => 0, 'fail_count' => 0, 'streak' => 0, 'easiness_factor' => 2.5, 'interval_days' => 1]
            );

            $progress->last_seen = now();

            $quality = $isCorrect ? 5 : 1;
            $progress->applySM2($quality);

            if ($isCorrect) {
                $progress->success_count++;
                $progress->streak++;
            } else {
                $progress->fail_count++;
                $progress->streak = 0;
            }

            $progress->save();

            // Mode apprentissage : retirer l'item du pool si réponse correcte
            if (session('anki_learn_mode') && $isCorrect) {
                $remaining = session('anki_learn_remaining', []);
                $remaining = array_values(array_filter($remaining, fn($id) => $id !== $data['item_id']));
                session(['anki_learn_remaining' => $remaining]);
            }

            // Stats de session globales
            if ($isCorrect) {
                session(['anki_session_correct' => session('anki_session_correct', 0) + 1]);
                session(['anki_session_streak'  => session('anki_session_streak',  0) + 1]);
            } else {
                session(['anki_session_wrong'  => session('anki_session_wrong', 0) + 1]);
                session(['anki_session_streak' => 0]);
            }

            return response()->json([
                'is_correct'      => $isCorrect,
                'session_correct' => session('anki_session_correct'),
                'session_wrong'   => session('anki_session_wrong'),
                'session_streak'  => session('anki_session_streak'),
                'is_mastered'     => $progress->isMastered(),
                'learn_remaining' => session('anki_learn_remaining') ? count(session('anki_learn_remaining')) : null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Anki submit error', ['message' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lance une session de révision rapide sur les items ratés non maîtrisés.
     */
    public function reviewStart(Module $module)
    {
        $this->authorize($module);

        $itemIds = $module->items()->pluck('id')->toArray();

        $reviewItemIds = Progress::where('user_id', Auth::id())
            ->whereIn('item_id', $itemIds)
            ->where('fail_count', '>', 0)
            ->where('streak', '<', 3)
            ->pluck('item_id')
            ->toArray();

        if (empty($reviewItemIds)) {
            return redirect()->route('anki.show', $module)
                ->with('success', 'Aucun item à réviser - tous vos items sont maîtrisés !');
        }

        shuffle($reviewItemIds);

        session([
            'anki_module_id'       => $module->id,
            'anki_mode'            => 'random',
            'anki_learn_mode'      => true,
            'anki_learn_remaining' => $reviewItemIds,
            'anki_learn_total'     => count($reviewItemIds),
            'anki_session_correct' => 0,
            'anki_session_wrong'   => 0,
            'anki_session_streak'  => 0,
        ]);

        return redirect()->route('anki.question', $module);
    }

    /**
     * Quitter une session Anki.
     */
    public function quit(Module $module)
    {
        $this->authorize($module);

        session()->forget(['anki_module_id', 'anki_mode', 'anki_learn_mode', 'anki_learn_remaining', 'anki_learn_total', 'anki_session_correct', 'anki_session_wrong', 'anki_session_streak']);

        return redirect()
            ->route('modules.show', $module)
            ->with('success', 'Session Anki terminée.');
    }

    /**
     * Vérifie que l'utilisateur peut accéder au module.
     */
    private function authorize(Module $module): void
    {
        $user = Auth::user();
        $role = $user ? $user->role : null;

        if (!$module->is_public && $module->owner_id !== $user?->id && !$user?->is_admin) {
            abort(403, 'Ce module est prive.');
        }

        if ($module->owner_id === $user?->id) {
            if ($role && !$role->can_train_own && !$user->is_admin) {
                abort(403, 'Vous n\'avez pas la permission de vous entrainer sur vos modules.');
            }
        } else {
            if ($role && !$role->can_train_public && !$user->is_admin) {
                abort(403, 'Vous n\'avez pas la permission de vous entrainer sur les modules publics.');
            }
        }
    }
}
