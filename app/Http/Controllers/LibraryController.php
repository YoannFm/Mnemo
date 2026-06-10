<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

/**
 * Contrôleur de la bibliothèque publique.
 * Affiche tous les modules marqués comme publics par n'importe quel utilisateur.
 * Permet à un utilisateur de parcourir et de tester les modules des autres.
 */
class LibraryController extends Controller
{
    /**
     * Affiche la liste de tous les modules publics.
     * Inclut un champ de recherche par titre/description.
     */
    public function index(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $role = $user ? $user->role : null;
        if ($role && !$role->can_access_library && !$user->is_admin) {
            abort(403, 'Vous n\'avez pas acces a la bibliotheque publique.');
        }

        $search  = $request->input('q');
        $tagIds  = array_filter((array) $request->input('tags', []));

        $modules = Module::where('is_public', true)
            ->with('owner')
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(!empty($tagIds), function ($query) use ($tagIds) {
                foreach ($tagIds as $tagId) {
                    $query->whereHas('tags', fn($q) => $q->where('tags.id', $tagId));
                }
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('library.index', compact('modules', 'search'));
    }
}
