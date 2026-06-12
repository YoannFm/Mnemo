<?php

namespace App\Http\Controllers;

use App\Models\SharedExam;
use App\Models\SharedExamAttempt;
use App\Services\QuizGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestExamController extends Controller
{
    private array $modeMap = [
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
    ];

    public function show(string $uuid)
    {
        $sharedExam = SharedExam::where('uuid', $uuid)->with('user', 'module')->firstOrFail();

        if ($sharedExam->isExpired()) {
            return view('shared-exam.guest.start', compact('sharedExam'))->with('expired', true);
        }

        return view('shared-exam.guest.start', compact('sharedExam'));
    }

    public function start(string $uuid)
    {
        $sharedExam = SharedExam::where('uuid', $uuid)->with('module')->firstOrFail();

        if ($sharedExam->isExpired()) {
            abort(410, 'Ce lien d\'examen a expiré.');
        }

        $attemptsDone = $sharedExam->attempts()->where('user_id', Auth::id())->count();
        if ($attemptsDone >= $sharedExam->max_attempts) {
            return redirect()->route('guest.exam.show', $uuid)
                ->with('error', 'Vous avez atteint le nombre maximum de tentatives pour cet examen.');
        }

        $module   = $sharedExam->module;
        $allItems = $module->items()->get();

        if ($allItems->isEmpty()) {
            return redirect()->route('guest.exam.show', $uuid)
                ->with('error', 'Ce module ne contient aucun item.');
        }

        $mode      = $sharedExam->mode;
        $items     = $allItems->shuffle();
        $questions = [];

        foreach ($items as $item) {
            if ($mode === 'random' || !isset($this->modeMap[$mode])) {
                $types = QuizGenerator::getQuestionTypes();
            } else {
                $types = $this->modeMap[$mode];
            }
            $questionType = $types[array_rand($types)];
            $q            = QuizGenerator::generateQuestion($module, $questionType, $item);
            if (!isset($q['error'])) {
                $questions[] = $q;
            }
        }

        session([
            'guest_exam_uuid'      => $uuid,
            'guest_exam_module_id' => $module->id,
            'guest_exam_questions' => $questions,
            'guest_exam_current'   => 0,
            'guest_exam_answers'   => [],
            'guest_exam_name'      => Auth::user()->name,
        ]);

        return redirect()->route('guest.exam.question', $uuid);
    }

    public function question(string $uuid)
    {
        $sharedExam = SharedExam::where('uuid', $uuid)->firstOrFail();

        if (session('guest_exam_uuid') !== $uuid) {
            return redirect()->route('guest.exam.show', $uuid);
        }

        $current   = session('guest_exam_current', 0);
        $questions = session('guest_exam_questions', []);
        $total     = count($questions);

        if ($current >= $total) {
            return redirect()->route('guest.exam.finish', $uuid);
        }

        $question            = $questions[$current];
        $question['current'] = $current + 1;
        $question['total']   = $total;

        return view('shared-exam.guest.question', compact('sharedExam', 'question'));
    }

    public function answer(Request $request, string $uuid)
    {
        $sharedExam = SharedExam::where('uuid', $uuid)->firstOrFail();

        if (session('guest_exam_uuid') !== $uuid) {
            return redirect()->route('guest.exam.show', $uuid);
        }

        $validated = $request->validate(['answer' => 'required|integer|min:0|max:7']);
        $current   = session('guest_exam_current', 0);
        $questions = session('guest_exam_questions', []);
        $question  = $questions[$current];

        $isCorrect = QuizGenerator::validateAnswer($question, $validated['answer']);
        $answers   = session('guest_exam_answers', []);
        $answers[$current] = [
            'item_id'          => $question['item_id'],
            'question_type'    => $question['question_type'],
            'user_answer'      => $question['options'][$validated['answer']],
            'correct_answer'   => $question['correct_answer'],
            'is_correct'       => $isCorrect,
            'field_question'   => $question['field_question'],
            'field_answer'     => $question['field_answer'],
            'question_text'    => $question['question_text'],
            'question_content' => $question['question_content'] ?? null,
        ];

        session([
            'guest_exam_answers' => $answers,
            'guest_exam_current' => $current + 1,
        ]);

        return redirect()->route('guest.exam.question', $uuid);
    }

    public function finish(string $uuid)
    {
        $sharedExam = SharedExam::where('uuid', $uuid)->with('user')->firstOrFail();

        if (session('guest_exam_uuid') !== $uuid) {
            return redirect()->route('guest.exam.show', $uuid);
        }

        $answers   = session('guest_exam_answers', []);
        $total     = count($answers);

        if ($total === 0) {
            return redirect()->route('guest.exam.show', $uuid)
                ->with('error', 'L\'examen n\'a pas été complété.');
        }

        $score     = collect($answers)->where('is_correct', true)->count();
        $guestName = session('guest_exam_name', 'Invité');

        $attempt = SharedExamAttempt::create([
            'shared_exam_id' => $sharedExam->id,
            'user_id'        => Auth::id(),
            'guest_name'     => $guestName,
            'answers'        => $answers,
            'score'          => $score,
            'total'          => $total,
            'finished_at'    => now(),
        ]);

        session()->forget([
            'guest_exam_uuid',
            'guest_exam_module_id',
            'guest_exam_questions',
            'guest_exam_current',
            'guest_exam_answers',
            'guest_exam_name',
        ]);

        // Classement parmi tous les participants
        $allAttempts = $sharedExam->attempts()->whereNotNull('finished_at')->orderBy('score', 'desc')->orderBy('finished_at')->get();
        $rank = $allAttempts->search(fn($a) => $a->id === $attempt->id);
        $rank = $rank !== false ? $rank + 1 : null;
        $totalParticipants = $allAttempts->count();

        return view('shared-exam.guest.finish', compact('sharedExam', 'attempt', 'rank', 'totalParticipants'));
    }
}
