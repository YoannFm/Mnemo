<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\SharedExam;
use App\Models\SharedExamAttempt;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $attempts = $sharedExam->attempts()->orderBy('created_at', 'desc')->get();
        $label    = $sharedExam->label ?? 'examen';
        $filename = 'notes-' . Str::slug($label) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attempts) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Participant', 'Note /20'], ';');

            foreach ($attempts as $attempt) {
                fputcsv($handle, [
                    $attempt->guest_name,
                    $attempt->grade,
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export(SharedExam $sharedExam)
    {
        if ($sharedExam->user_id !== Auth::id()) {
            abort(403);
        }

        $attempts = $sharedExam->attempts()->orderBy('created_at', 'desc')->get();
        $label    = $sharedExam->label ?? 'examen';
        $filename = 'resultats-' . Str::slug($label) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attempts, $sharedExam) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['Participant', 'Score', 'Note /20', '%', 'Date'], ';');

            foreach ($attempts as $attempt) {
                fputcsv($handle, [
                    $attempt->guest_name,
                    $attempt->score . '/' . $attempt->total,
                    $attempt->grade,
                    $attempt->percentage . '%',
                    $attempt->finished_at ? $attempt->finished_at->format('d/m/Y H:i') : '-',
                ], ';');

                if ($attempt->answers && count($attempt->answers) > 0) {
                    fputcsv($handle, ['', 'Question', 'Réponse donnée', 'Bonne réponse', 'Résultat'], ';');
                    foreach ($attempt->answers as $ans) {
                        $content = $ans['question_content'] ?? null;
                        $isPhoto = $content && str_contains((string) $content, '/storage/');
                        $questionLabel = $isPhoto
                            ? ($ans['question_text'] ?? '-')
                            : ($content ?? $ans['question_text'] ?? '-');

                        fputcsv($handle, [
                            '',
                            $questionLabel,
                            $ans['user_answer'] ?? '-',
                            $ans['correct_answer'] ?? '-',
                            $ans['is_correct'] ? 'Réussi' : 'Échoué',
                        ], ';');
                    }
                    fputcsv($handle, [], ';');
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
}
