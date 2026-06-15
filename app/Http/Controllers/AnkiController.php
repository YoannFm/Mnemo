<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\AnkiSession;
use App\Models\Module;
use App\Models\Progress;
use App\Services\QuizGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnkiController extends Controller
{
    public function show(Module $module)
    {
        $this->authorize($module);

        if ($module->items()->count() === 0) {
            return redirect()
                ->route('modules.show', $module)
                ->with('error', 'Vous devez d\'abord ajouter des items au module.');
        }

        $ankiSession = AnkiSession::where('user_id', Auth::id())
            ->where('module_id', $module->id)
            ->first();

        $itemIds      = $module->items()->pluck('id');
        $totalCount   = $itemIds->count();
        $progressAll  = Progress::where('user_id', Auth::id())
            ->whereIn('item_id', $itemIds)
            ->get();

        $masteredCount = $progressAll->filter(fn($p) => $p->success_count > 0)->count();
        $dueCount      = $progressAll->filter(fn($p) => $p->isDueForReview())->count()
                         + ($totalCount - $progressAll->count()); // nouveaux = dus par defaut
        $newCount      = $totalCount - $progressAll->count();

        return view('quiz.anki.setup', compact(
            'module', 'ankiSession', 'totalCount', 'masteredCount', 'dueCount', 'newCount'
        ));
    }

    public function start(Request $request, Module $module)
    {
        $this->authorize($module);

        $validModes = [
            'random', 'photo_to_name_fr', 'photo_to_name_alt', 'photo_to_function',
            'function_to_photo', 'function_to_name_fr', 'function_to_name_alt',
            'name_fr_to_name_alt', 'name_fr_to_photo', 'name_fr_to_function',
            'name_alt_to_photo', 'name_alt_to_function', 'name_alt_to_name_fr',
            'audio_to_name_fr', 'audio_to_name_alt', 'name_fr_to_audio', 'name_alt_to_audio',
        ];
        $rawMode = $request->input('mode', 'random');
        $mode    = in_array($rawMode, $validModes, true) ? $rawMode : 'random';

        $learnRemaining = null;
        $learnTotal     = null;

        if ($mode !== 'random') {
            $allItemIds     = $module->items()->pluck('id')->toArray();
            shuffle($allItemIds);
            $learnRemaining = $allItemIds;
            $learnTotal     = count($allItemIds);

            session([
                'anki_learn_mode'      => true,
                'anki_learn_remaining' => $learnRemaining,
                'anki_learn_total'     => $learnTotal,
            ]);
        } else {
            session()->forget(['anki_learn_mode', 'anki_learn_remaining', 'anki_learn_total']);
        }

        session([
            'anki_module_id'       => $module->id,
            'anki_mode'            => $mode,
            'anki_session_correct' => 0,
            'anki_session_wrong'   => 0,
            'anki_session_streak'  => 0,
        ]);

        AnkiSession::updateOrCreate(
            ['user_id' => Auth::id(), 'module_id' => $module->id],
            ['mode' => $mode, 'learn_remaining' => $learnRemaining, 'learn_total' => $learnTotal]
        );

        LogHelper::log('started_anki', 'module', $module->id, ['mode' => $mode]);

        return redirect()->route('anki.question', $module);
    }

    public function question(Module $module)
    {
        $this->authorize($module);

        // Restauration depuis la DB si la session PHP est absente ou pointe sur un autre module
        if (!session()->has('anki_module_id') || session('anki_module_id') !== $module->id) {
            $saved = AnkiSession::where('user_id', Auth::id())
                ->where('module_id', $module->id)
                ->first();

            if (!$saved) {
                return redirect()->route('anki.show', $module)
                    ->with('info', 'Choisissez un mode pour commencer.');
            }

            session([
                'anki_module_id'       => $module->id,
                'anki_mode'            => $saved->mode,
                'anki_session_correct' => 0,
                'anki_session_wrong'   => 0,
                'anki_session_streak'  => 0,
            ]);

            if ($saved->isLearnMode()) {
                // Fallback si learn_total est null (ne pas laisser null dans session car session('key', 0) retourne null si null stocke)
                $learnTotal = $saved->learn_total ?? count($saved->learn_remaining);
                session([
                    'anki_learn_mode'      => true,
                    'anki_learn_remaining' => $saved->learn_remaining,
                    'anki_learn_total'     => $learnTotal,
                ]);
            } else {
                session()->forget(['anki_learn_mode', 'anki_learn_remaining', 'anki_learn_total']);
            }
        }

        $mode      = session('anki_mode', 'random');
        $learnMode = session('anki_learn_mode', false);

        $modeMap = [
            'photo_to_name_fr'     => ['Q1'],
            'photo_to_name_alt'    => ['Q8'],
            'photo_to_function'    => ['Q2'],
            'function_to_photo'    => ['Q3'],
            'function_to_name_fr'  => ['Q6'],
            'function_to_name_alt' => ['Q11'],
            'name_fr_to_name_alt'  => ['Q4'],
            'name_fr_to_photo'     => ['Q9'],
            'name_fr_to_function'  => ['Q10'],
            'name_alt_to_photo'    => ['Q5'],
            'name_alt_to_function' => ['Q11'],
            'name_alt_to_name_fr'  => ['Q7'],
            'audio_to_name_fr'     => ['Q13'],
            'audio_to_name_alt'    => ['Q14'],
            'name_fr_to_audio'     => ['Q15'],
            'name_alt_to_audio'    => ['Q16'],
        ];

        if ($mode === 'random' || !isset($modeMap[$mode])) {
            $mediaAnswerTypes = ['Q3', 'Q5', 'Q9', 'Q15', 'Q16'];
            $types = array_values(array_diff(QuizGenerator::getQuestionTypes(), $mediaAnswerTypes));
        } else {
            $types = $modeMap[$mode];
        }

        $questionType = $types[array_rand($types)];
        $allItems     = $module->items()->get();

        $fieldQuestion = \App\Services\QuizGenerator::getFieldQuestion($questionType);
        $eligibleItems = $fieldQuestion
            ? $allItems->filter(fn($i) => !empty($i->{$fieldQuestion}))
            : $allItems;
        if ($eligibleItems->isEmpty()) {
            $eligibleItems = $allItems;
        }

        if ($learnMode) {
            $remaining  = session('anki_learn_remaining', []);
            $learnTotal = session('anki_learn_total', 0);

            if (empty($remaining)) {
                $this->clearSession($module);
                return redirect()->route('modules.show', $module)
                    ->with('success', 'Bravo ! Vous avez maitrise tous les items de ce module.');
            }

            $eligibleRemaining = array_values(
                array_filter($remaining, fn($id) => $eligibleItems->contains('id', $id))
            );
            if (empty($eligibleRemaining)) {
                $eligibleRemaining = $remaining;
            }
            $targetItemId = $eligibleRemaining[array_rand($eligibleRemaining)];
            $targetItem   = $allItems->firstWhere('id', $targetItemId);
        } else {
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
                    // Item maitrise et non-du : exclu jusqu'a la prochaine echeance
                    continue;
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

            if (empty($weightedPool)) {
                return redirect()->route('anki.show', $module)
                    ->with('success', 'Tous les items sont maitrisés ! Revenez quand les prochaines révisions sont dues.');
            }

            $targetItem = $weightedPool[array_rand($weightedPool)];
        }

        $question = QuizGenerator::generateQuestion($module, $questionType, $targetItem, $types);

        if (isset($question['error'])) {
            return redirect()->route('modules.show', $module)->with('error', $question['error']);
        }

        $question['session'] = [
            'correct' => session('anki_session_correct', 0),
            'wrong'   => session('anki_session_wrong', 0),
            'streak'  => session('anki_session_streak', 0),
        ];

        if ($learnMode) {
            $question['learn_remaining'] = count($remaining);
            $question['learn_total']     = $learnTotal;
        } else {
            // Mode aleatoire : progression globale
            $learnedCount  = isset($progressMap)
                ? $progressMap->filter(fn($p) => $p->success_count > 0)->count()
                : 0;
            $masteredCount = isset($progressMap)
                ? $progressMap->filter(fn($p) => $p->isMastered())->count()
                : 0;
            $question['learned_count']  = $learnedCount;
            $question['mastered_count'] = $masteredCount;
            $question['total_count']    = $allItems->count();
        }

        return view('quiz.anki.question', compact('module', 'question'));
    }

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

            $isCorrect = (bool) $data['knows'];
            $item      = $module->items()->findOrFail($data['item_id']);

            $progress = Progress::firstOrCreate(
                ['user_id' => Auth::id(), 'item_id' => $item->id],
                ['success_count' => 0, 'fail_count' => 0, 'streak' => 0, 'easiness_factor' => 2.5, 'interval_days' => 1]
            );

            $progress->last_seen = now();
            $progress->applySM2($isCorrect ? 5 : 1);

            if ($isCorrect) {
                $progress->success_count++;
                $progress->streak++;
            } else {
                $progress->fail_count++;
                $progress->streak = 0;
            }

            $progress->save();

            if (session('anki_learn_mode') && $isCorrect) {
                $remaining = session('anki_learn_remaining', []);
                $remaining = array_values(array_filter($remaining, fn($id) => $id !== $item->id));
                session(['anki_learn_remaining' => $remaining]);

                // Synchroniser la liste restante en DB
                AnkiSession::where('user_id', Auth::id())
                    ->where('module_id', $module->id)
                    ->update(['learn_remaining' => $remaining]);
            }

            if ($isCorrect) {
                session(['anki_session_correct' => session('anki_session_correct', 0) + 1]);
                session(['anki_session_streak'  => session('anki_session_streak',  0) + 1]);
            } else {
                session(['anki_session_wrong'  => session('anki_session_wrong', 0) + 1]);
                session(['anki_session_streak' => 0]);
            }

            $learnRemaining      = session('anki_learn_remaining');
            $learnRemainingCount = $learnRemaining !== null ? count($learnRemaining) : null;

            if (session('anki_learn_mode') && $learnRemainingCount === 0) {
                LogHelper::log('completed_anki', 'module', $module->id, [
                    'correct' => session('anki_session_correct'),
                    'wrong'   => session('anki_session_wrong'),
                ]);
                // Session terminee : supprimer l'entree DB pour repartir proprement
                AnkiSession::where('user_id', Auth::id())
                    ->where('module_id', $module->id)
                    ->delete();
            }

            return response()->json([
                'is_correct'      => $isCorrect,
                'session_correct' => session('anki_session_correct'),
                'session_wrong'   => session('anki_session_wrong'),
                'session_streak'  => session('anki_session_streak'),
                'is_mastered'     => $progress->isMastered(),
                'learn_remaining' => $learnRemainingCount,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Anki submit error', ['message' => $e->getMessage(), 'user_id' => Auth::id()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

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
                ->with('success', 'Aucun item a réviser - tous vos items sont maitrisés !');
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

        AnkiSession::updateOrCreate(
            ['user_id' => Auth::id(), 'module_id' => $module->id],
            ['mode' => 'random', 'learn_remaining' => $reviewItemIds, 'learn_total' => count($reviewItemIds)]
        );

        LogHelper::log('started_anki_review', 'module', $module->id);

        return redirect()->route('anki.question', $module);
    }

    public function quit(Module $module)
    {
        $this->authorize($module);

        // Sauvegarder l'etat courant pour permettre de reprendre plus tard
        if (session('anki_module_id') === $module->id) {
            $learnRemaining = session('anki_learn_mode') ? session('anki_learn_remaining') : null;
            AnkiSession::updateOrCreate(
                ['user_id' => Auth::id(), 'module_id' => $module->id],
                [
                    'mode'           => session('anki_mode', 'random'),
                    'learn_remaining' => $learnRemaining,
                    'learn_total'    => session('anki_learn_mode') ? session('anki_learn_total') : null,
                ]
            );
        }

        session()->forget([
            'anki_module_id', 'anki_mode', 'anki_learn_mode',
            'anki_learn_remaining', 'anki_learn_total',
            'anki_session_correct', 'anki_session_wrong', 'anki_session_streak',
        ]);

        return redirect()
            ->route('anki.show', $module)
            ->with('success', 'Session mise en pause. Vous pouvez reprendre quand vous voulez.');
    }

    private function clearSession(Module $module): void
    {
        AnkiSession::where('user_id', Auth::id())
            ->where('module_id', $module->id)
            ->delete();

        session()->forget([
            'anki_module_id', 'anki_mode', 'anki_learn_mode',
            'anki_learn_remaining', 'anki_learn_total',
            'anki_session_correct', 'anki_session_wrong', 'anki_session_streak',
        ]);
    }

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
