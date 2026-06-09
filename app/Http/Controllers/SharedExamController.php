<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\SharedExam;
use App\Models\SharedExamAttempt;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SharedExamController extends Controller
{
    public function create(Request $request, Module $module)
    {
        if ($module->owner_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'mode'       => 'required|string',
            'label'      => 'nullable|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $sharedExam = SharedExam::create([
            'uuid'       => (string) Str::uuid(),
            'user_id'    => Auth::id(),
            'module_id'  => $module->id,
            'mode'       => $validated['mode'],
            'label'      => $validated['label'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        $link = route('guest.exam.show', $sharedExam->uuid);

        return redirect()
            ->route('modules.show', $module)
            ->with('success', 'Lien d\'examen créé : ' . $link);
    }

    public function results(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $attempts = $sharedExam->attempts()->orderBy('created_at', 'desc')->get();

        return view('shared-exam.results', compact('sharedExam', 'attempts'));
    }

    public function destroy(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $sharedExam->delete();

        return redirect()->back()->with('success', 'Lien d\'examen supprimé.');
    }

    public function addAttempt(Request $request, SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate(['extra' => 'required|integer|min:1|max:10']);
        $sharedExam->increment('max_attempts', $validated['extra']);

        return redirect()->back()->with('success', 'Tentatives supplémentaires accordées.');
    }

    public function resetAttempt(SharedExam $sharedExam, SharedExamAttempt $attempt)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $attempt->delete();

        return redirect()->back()->with('success', 'Tentative supprimée, l\'utilisateur peut repasser l\'examen.');
    }

    public function sendResults(SharedExam $sharedExam, SharedExamAttempt $attempt)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$attempt->user_id) {
            return redirect()->back()->with('error', 'Impossible d\'envoyer les résultats : utilisateur introuvable.');
        }

        $pct   = $attempt->percentage;
        $label = $sharedExam->label ?? 'Examen partagé';

        UserNotification::create([
            'user_id' => $attempt->user_id,
            'title'   => 'Résultats de votre examen',
            'message' => "Votre score pour « {$label} » (module : {$sharedExam->module->title}) : {$attempt->score}/{$attempt->total} ({$pct}%).",
            'type'    => 'info',
        ]);

        $attempt->update(['results_sent_at' => now()]);

        return redirect()->back()->with('success', 'Résultats envoyés à ' . $attempt->guest_name . '.');
    }

    public function exportGrades(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) abort(403);

        $attempts = $sharedExam->attempts()->orderBy('created_at', 'desc')->get();
        $label    = $sharedExam->label ?? 'examen';

        return response()->stream(function () use ($attempts) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($h, ['Participant', 'Note /20'], ';');
            foreach ($attempts as $a) {
                fputcsv($h, [$a->guest_name, $a->grade], ';');
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="notes-' . Str::slug($label) . '.csv"',
        ]);
    }

    public function export(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) abort(403);

        $attempts = $sharedExam->attempts()->orderBy('created_at', 'desc')->get();
        $label    = $sharedExam->label ?? 'examen';

        return response()->stream(function () use ($attempts) {
            $h = fopen('php://output', 'w');
            fprintf($h, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($h, ['Participant', 'Score', 'Note /20', '%', 'Date', 'Question', 'Réponse donnée', 'Bonne réponse', 'Résultat'], ';');

            foreach ($attempts as $attempt) {
                $answers = $attempt->answers ?? [];
                $first   = true;
                if (count($answers) > 0) {
                    foreach ($answers as $ans) {
                        $content = $ans['question_content'] ?? null;
                        $isPhoto = $content && str_contains((string) $content, '/storage/');
                        $q = $isPhoto ? ($ans['question_text'] ?? '-') : ($content ?? $ans['question_text'] ?? '-');
                        fputcsv($h, [
                            $first ? $attempt->guest_name : '',
                            $first ? $attempt->score . '/' . $attempt->total : '',
                            $first ? $attempt->grade : '',
                            $first ? $attempt->percentage . '%' : '',
                            $first ? ($attempt->finished_at?->format('d/m/Y H:i') ?? '-') : '',
                            $q,
                            $ans['user_answer'] ?? '-',
                            $ans['correct_answer'] ?? '-',
                            $ans['is_correct'] ? 'Réussi' : 'Échoué',
                        ], ';');
                        $first = false;
                    }
                } else {
                    fputcsv($h, [$attempt->guest_name, $attempt->score . '/' . $attempt->total, $attempt->grade, $attempt->percentage . '%', $attempt->finished_at?->format('d/m/Y H:i') ?? '-', '-', '-', '-', '-'], ';');
                }
                fputcsv($h, [], ';');
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="resultats-' . Str::slug($label) . '.csv"',
        ]);
    }

    public function exportExcel(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) abort(403);

        $attempts = $sharedExam->attempts()->orderBy('created_at', 'desc')->get();
        $label    = $sharedExam->label ?? 'examen';

        $spreadsheet = new Spreadsheet();

        // Feuille 1 : Notes
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Notes');
        $sheet1->fromArray(['Participant', 'Score', 'Note /20', '%', 'Date'], null, 'A1');
        $row = 2;
        foreach ($attempts as $a) {
            $sheet1->fromArray([
                $a->guest_name,
                $a->score . '/' . $a->total,
                $a->grade,
                $a->percentage . '%',
                $a->finished_at?->format('d/m/Y H:i') ?? '-',
            ], null, 'A' . $row);
            $row++;
        }

        // Feuille 2 : Réponses détaillées
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Réponses');
        $sheet2->fromArray(['Participant', 'Score', 'Note /20', '%', 'Date', 'Question', 'Réponse donnée', 'Bonne réponse', 'Résultat'], null, 'A1');
        $row = 2;
        foreach ($attempts as $attempt) {
            $answers = $attempt->answers ?? [];
            $first   = true;
            if (count($answers) > 0) {
                foreach ($answers as $ans) {
                    $content = $ans['question_content'] ?? null;
                    $isPhoto = $content && str_contains((string) $content, '/storage/');
                    $q = $isPhoto ? ($ans['question_text'] ?? '-') : ($content ?? $ans['question_text'] ?? '-');
                    $sheet2->fromArray([
                        $first ? $attempt->guest_name : '',
                        $first ? $attempt->score . '/' . $attempt->total : '',
                        $first ? $attempt->grade : '',
                        $first ? $attempt->percentage . '%' : '',
                        $first ? ($attempt->finished_at?->format('d/m/Y H:i') ?? '-') : '',
                        $q,
                        $ans['user_answer'] ?? '-',
                        $ans['correct_answer'] ?? '-',
                        $ans['is_correct'] ? 'Réussi' : 'Échoué',
                    ], null, 'A' . $row);
                    $first = false;
                    $row++;
                }
            } else {
                $sheet2->fromArray([$attempt->guest_name, $attempt->score . '/' . $attempt->total, $attempt->grade, $attempt->percentage . '%', $attempt->finished_at?->format('d/m/Y H:i') ?? '-', '-', '-', '-', '-'], null, 'A' . $row);
                $row++;
            }
            $row++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'resultats-' . Str::slug($label) . '.xlsx';
        $path = storage_path('app/temp/' . $filename);
        if (!is_dir(storage_path('app/temp'))) mkdir(storage_path('app/temp'), 0755, true);

        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function sendAllResults(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $sharedExam->load('module');
        $label   = $sharedExam->label ?? 'Examen partagé';
        $sent    = 0;

        foreach ($sharedExam->attempts()->whereNotNull('user_id')->whereNull('results_sent_at')->get() as $attempt) {
            $pct = $attempt->percentage;
            UserNotification::create([
                'user_id' => $attempt->user_id,
                'title'   => 'Résultats de votre examen',
                'message' => "Votre score pour « {$label} » (module : {$sharedExam->module->title}) : {$attempt->score}/{$attempt->total} ({$pct}%).",
                'type'    => 'info',
            ]);
            $attempt->update(['results_sent_at' => now()]);
            $sent++;
        }

        return redirect()->back()->with('success', $sent > 0 ? "{$sent} résultat(s) envoyé(s)." : 'Tous les résultats ont déjà été envoyés.');
    }

    public function exportPdf(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) abort(403);

        $sharedExam->load('module');
        $attempts = $sharedExam->attempts()->orderBy('score', 'desc')->get();
        $label    = $sharedExam->label ?? 'examen';

        $pdf = Pdf::loadView('shared-exam.pdf', compact('sharedExam', 'attempts'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download('resultats-' . Str::slug($label) . '.pdf');
    }
}
