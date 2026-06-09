<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Score;
use App\Models\SharedExam;
use App\Services\QuizGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function show(Module $module)
    {
        $this->authorize($module);
        $itemCount = $module->items()->count();
        if ($itemCount < 4) {
            return redirect()->route('modules.show', $module)->with('error', 'Il faut au moins 4 items pour passer un examen.');
        }

        $sharedExams = Auth::id() === $module->owner_id
            ? SharedExam::where('module_id', $module->id)->withCount('attempts')->latest()->get()
            : collect();

        return view('quiz.exam.start', compact('module', 'itemCount', 'sharedExams'));
    }

    public function start(Request $request, Module $module)
    {
        $this->authorize($module);
        $data = $request->validate(['mode' => 'required|string']);
        $mode = $data['mode'];

        $allItems = $module->items()->get();
        $modeMap = [
            'photo_to_name_fr'    => ['Q1'],
            'photo_to_name_en'    => ['Q8'],
            'photo_to_function'   => ['Q2'],
            'function_to_photo'   => ['Q3'],
            'function_to_name_fr' => ['Q6'],
            'function_to_name_en' => ['Q11'],
            'name_fr_to_name_en'  => ['Q4'],
            'name_fr_to_photo'    => ['Q9'],
            'name_fr_to_function' => ['Q10'],
            'name_en_to_photo'    => ['Q5'],
            'name_en_to_function' => ['Q11'],
            'name_en_to_name_fr'  => ['Q7'],
        ];

        // Generate one question per item (shuffle items)
        $items = $allItems->shuffle();
        $questions = [];
        foreach ($items as $item) {
            if ($mode === 'random' || !isset($modeMap[$mode])) {
                $types = QuizGenerator::getQuestionTypes();
            } else {
                $types = $modeMap[$mode];
            }
            $questionType = $types[array_rand($types)];
            $q = QuizGenerator::generateQuestion($module, $questionType, $item);
            if (!isset($q['error'])) {
                $questions[] = $q;
            }
        }

        session([
            'exam_module_id' => $module->id,
            'exam_questions' => $questions,
            'exam_current'   => 0,
            'exam_answers'   => [],
            'exam_mode'      => $mode,
        ]);

        return redirect()->route('exam.question', $module);
    }

    public function question(Module $module)
    {
        $this->authorize($module);
        if (!session()->has('exam_module_id') || session('exam_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucun examen en cours.');
        }

        $current = session('exam_current', 0);
        $questions = session('exam_questions', []);
        $total = count($questions);

        if ($current >= $total) {
            return redirect()->route('exam.result', $module);
        }

        $question = $questions[$current];
        $question['current'] = $current + 1;
        $question['total']   = $total;

        return view('quiz.exam.question', compact('module', 'question'));
    }

    public function submit(Request $request, Module $module)
    {
        $this->authorize($module);
        if (!session()->has('exam_module_id') || session('exam_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucun examen en cours.');
        }

        $validated = $request->validate(['answer' => 'required|integer|min:0|max:3']);
        $current = session('exam_current', 0);
        $questions = session('exam_questions', []);
        $question = $questions[$current];

        $isCorrect = QuizGenerator::validateAnswer($question, $validated['answer']);
        $answers = session('exam_answers', []);
        $answers[$current] = [
            'item_id'        => $question['item_id'],
            'question_type'  => $question['question_type'],
            'user_answer'    => $question['options'][$validated['answer']],
            'correct_answer' => $question['correct_answer'],
            'is_correct'     => $isCorrect,
            'field_question' => $question['field_question'],
            'field_answer'   => $question['field_answer'],
            'question_text'  => $question['question_text'],
        ];

        session(['exam_answers' => $answers, 'exam_current' => $current + 1]);
        return redirect()->route('exam.question', $module);
    }

    public function result(Module $module)
    {
        $this->authorize($module);
        if (!session()->has('exam_module_id') || session('exam_module_id') !== $module->id) {
            return redirect()->route('modules.show', $module)->with('error', 'Aucun examen en cours.');
        }

        $answers = session('exam_answers', []);
        $total   = count($answers);
        $score   = collect($answers)->where('is_correct', true)->count();

        Score::create(['user_id' => Auth::id(), 'module_id' => $module->id, 'score' => $score, 'total' => $total]);

        session()->forget(['exam_module_id', 'exam_questions', 'exam_current', 'exam_answers', 'exam_mode']);

        $percentage = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        return view('quiz.exam.result', compact('module', 'score', 'total', 'percentage', 'answers'));
    }

    private function authorize(Module $module): void
    {
        $user = Auth::user();
        $role = $user ? $user->role : null;

        if (!$module->is_public && $module->owner_id !== $user?->id && !$user?->is_admin) {
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
