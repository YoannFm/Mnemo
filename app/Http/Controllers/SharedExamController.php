<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\SharedExam;
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
}
