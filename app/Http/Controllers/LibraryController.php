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

        $search = $request->input('q');

        $modules = Module::where('is_public', true)
            ->with('owner')                // Chargement du propriétaire pour afficher son nom
            ->withCount('items')
            ->when($search, function ($query) use ($search) {
                // Recherche dans le titre et la description
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString(); // Conserver le paramètre de recherche dans la pagination

        return view('library.index', compact('modules', 'search'));
    }
}
