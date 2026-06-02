<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur CRUD des modules.
 * Gère la création, l'affichage, la modification et la suppression des modules
 * appartenant à l'utilisateur connecté.
 */
class ModuleController extends Controller
{
    /**
     * Affiche la liste des modules de l'utilisateur connecté.
     * Triés du plus récent au plus ancien.
     */
    public function index()
    {
        // On récupère uniquement les modules appartenant à l'utilisateur connecté
        $modules = Auth::user()->modules()
            ->withCount('items') // Ajoute un attribut "items_count" à chaque module
            ->latest()
            ->paginate(12);

        return view('modules.index', compact('modules'));
    }

    /**
     * Affiche le formulaire de création d'un nouveau module.
     */
    public function create()
    {
        return view('modules.create');
    }

    /**
     * Enregistre un nouveau module en base de données.
     * Valide les données du formulaire avant insertion.
     */
    public function store(Request $request)
    {
        // Validation des champs du formulaire
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public'   => 'nullable|boolean',
        ]);

        // Création du module lié à l'utilisateur connecté
        Auth::user()->modules()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public'   => $request->boolean('is_public'),
        ]);

        return redirect()->route('modules.index')
            ->with('success', 'Module créé avec succès !');
    }

    /**
     * Affiche le détail d'un module avec la liste de ses items.
     * Vérifie que l'utilisateur est propriétaire ou que le module est public.
     */
    public function show(Module $module)
    {
        // Un utilisateur non connecté ou tiers ne peut voir qu'un module public
        $this->authorizeView($module);

        $items = $module->items()->paginate(20);

        return view('modules.show', compact('module', 'items'));
    }

    /**
     * Affiche le formulaire d'édition d'un module.
     * Seul le propriétaire peut modifier son module.
     */
    public function edit(Module $module)
    {
        $this->authorizeOwner($module);

        return view('modules.edit', compact('module'));
    }

    /**
     * Enregistre les modifications apportées à un module existant.
     */
    public function update(Request $request, Module $module)
    {
        $this->authorizeOwner($module);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public'   => 'nullable|boolean',
        ]);

        $module->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public'   => $request->boolean('is_public'),
        ]);

        return redirect()->route('modules.show', $module)
            ->with('success', 'Module mis à jour avec succès !');
    }

    /**
     * Supprime un module et tous ses items (cascade définie en BDD).
     * Seul le propriétaire peut supprimer son module.
     */
    public function destroy(Module $module)
    {
        $this->authorizeOwner($module);

        $module->delete();

        return redirect()->route('modules.index')
            ->with('success', 'Module supprimé.');
    }

    /**
     * Duplique un module public dans l'espace de l'utilisateur connecté.
     * Copie le module et tous ses items (sans les photos).
     */
    public function duplicate(Module $module)
    {
        // On peut dupliquer uniquement si le module est public ou si on en est l'auteur
        if (!$module->is_public && $module->owner_id !== Auth::id()) {
            abort(403, 'Ce module est privé.');
        }

        // Créer une copie du module (privée par défaut)
        $copy = Auth::user()->modules()->create([
            'title'       => $module->title . ' (copie)',
            'description' => $module->description,
            'is_public'   => false,
        ]);

        // Copier tous les items (sans photo car les fichiers ne sont pas dupliqués)
        foreach ($module->items as $item) {
            $copy->items()->create([
                'name_fr'       => $item->name_fr,
                'name_en'       => $item->name_en,
                'function_text' => $item->function_text,
                'photo_path'    => $item->photo_path, // Partager le même chemin de photo
            ]);
        }

        return redirect()->route('modules.show', $copy)
            ->with('success', 'Module dupliqué dans votre espace ! Vous pouvez maintenant l\'enrichir.');
    }

    // ─────────────────────────────────────────────
    // Méthodes privées de vérification d'accès
    // ─────────────────────────────────────────────

    /**
     * Vérifie que l'utilisateur connecté est le propriétaire du module.
     * Lance une exception 403 si ce n'est pas le cas.
     */
    private function authorizeOwner(Module $module): void
    {
        if ($module->owner_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }
    }

    /**
     * Vérifie que l'utilisateur peut voir ce module :
     * soit il en est le propriétaire, soit le module est public.
     */
    private function authorizeView(Module $module): void
    {
        if (!$module->is_public && $module->owner_id !== Auth::id()) {
            abort(403, 'Ce module est privé.');
        }
    }
}
