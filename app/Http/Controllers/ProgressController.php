<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Progress;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de progression et d'historique.
 * Affiche les statistiques d'apprentissage de l'utilisateur connecté.
 */
class ProgressController extends Controller
{
    /**
     * Affiche la progression globale de l'utilisateur.
     * - Items maîtrisés par module (streak >= 3)
     * - Historique de tous les scores
     */
    public function index()
    {
        $user = Auth::user();

        // Récupérer uniquement les modules sur lesquels l'utilisateur a commencé à s'entraîner
        $modules = $user->modules()
            ->withCount('items')
            ->get()
            ->map(function ($module) use ($user) {
                $itemIds = $module->items()->pluck('id');

                $progresses = Progress::where('user_id', $user->id)
                    ->whereIn('item_id', $itemIds)
                    ->get();

                $module->mastered_count  = $progresses->filter->isMastered()->count();
                $module->practiced_count = $progresses->count();
                $module->percent = $module->items_count > 0
                    ? (int) round(($module->mastered_count / $module->items_count) * 100)
                    : 0;

                return $module;
            })
            ->filter(fn($module) => $module->practiced_count > 0)
            ->values();

        // Historique des scores (20 derniers)
        $scores = Score::where('user_id', $user->id)
            ->with('module')
            ->latest()
            ->paginate(20);

        // Statistiques globales
        $stats = [
            'total_mastered'  => Progress::where('user_id', $user->id)->where('streak', '>=', 3)->count(),
            'total_practiced' => Progress::where('user_id', $user->id)->count(),
            'total_tests'     => Score::where('user_id', $user->id)->count(),
            'avg_score'       => Score::where('user_id', $user->id)->count() > 0
                ? (int) round(Score::where('user_id', $user->id)->avg('score') /
                    max(Score::where('user_id', $user->id)->avg('total'), 1) * 100)
                : 0,
        ];

        return view('progress.index', compact('modules', 'scores', 'stats'));
    }
}
