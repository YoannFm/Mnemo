<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Contrôleur CRUD des items.
 * Les items sont imbriqués dans un module (routes modules.items.*).
 * Gère l'upload, la compression et la suppression des photos.
 */
class ItemController extends Controller
{
    /**
     * Affiche la liste des items d'un module.
     * Redirige vers la page du module (les items y sont listés).
     */
    public function index(Module $module)
    {
        $this->authorizeOwner($module);

        return redirect()->route('modules.show', $module);
    }

    /**
     * Affiche le formulaire de création d'un item pour un module donné.
     */
    public function create(Module $module)
    {
        $this->authorizeOwner($module);

        return view('items.create', compact('module'));
    }

    /**
     * Enregistre un nouvel item en base de données.
     * Compresse et stocke la photo si elle est fournie.
     */
    public function store(Request $request, Module $module)
    {
        $this->authorizeOwner($module);

        // Validation des champs du formulaire
        $validated = $request->validate([
            'name_fr'       => 'required|string|max:255',
            'name_en'       => 'required|string|max:255',
            'function_text' => 'required|string|max:2000',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $photoPath = null;

        // Si une photo est fournie, on la compresse et on la stocke
        if ($request->hasFile('photo')) {
            $photoPath = $this->storePhoto($request->file('photo'));
        }

        // Création de l'item lié au module
        $module->items()->create([
            'name_fr'       => $validated['name_fr'],
            'name_en'       => $validated['name_en'],
            'function_text' => $validated['function_text'],
            'photo_path'    => $photoPath,
        ]);

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item ajouté avec succès !');
    }

    /**
     * Affiche le formulaire d'édition d'un item existant.
     */
    public function edit(Module $module, Item $item)
    {
        $this->authorizeOwner($module);
        $this->ensureBelongsToModule($item, $module);

        return view('items.edit', compact('module', 'item'));
    }

    /**
     * Enregistre les modifications d'un item.
     * Remplace la photo si une nouvelle est uploadée, supprime l'ancienne.
     */
    public function update(Request $request, Module $module, Item $item)
    {
        $this->authorizeOwner($module);
        $this->ensureBelongsToModule($item, $module);

        $validated = $request->validate([
            'name_fr'       => 'required|string|max:255',
            'name_en'       => 'required|string|max:255',
            'function_text' => 'required|string|max:2000',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $photoPath = $item->photo_path; // On garde l'ancienne photo par défaut

        // Si une nouvelle photo est envoyée, on supprime l'ancienne et on stocke la nouvelle
        if ($request->hasFile('photo')) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
            $photoPath = $this->storePhoto($request->file('photo'));
        }

        $item->update([
            'name_fr'       => $validated['name_fr'],
            'name_en'       => $validated['name_en'],
            'function_text' => $validated['function_text'],
            'photo_path'    => $photoPath,
        ]);

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item mis à jour !');
    }

    /**
     * Supprime un item et sa photo associée du disque.
     */
    public function destroy(Module $module, Item $item)
    {
        $this->authorizeOwner($module);
        $this->ensureBelongsToModule($item, $module);

        // Suppression physique de la photo du disque si elle existe
        if ($item->photo_path) {
            Storage::disk('public')->delete($item->photo_path);
        }

        $item->delete();

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item supprimé.');
    }

    // ─────────────────────────────────────────────
    // Méthodes privées utilitaires
    // ─────────────────────────────────────────────

    /**
     * Compresse et stocke une photo uploadée dans storage/public/items.
     * Redimensionne à 800px max en conservant les proportions.
     * Retourne le chemin relatif enregistré en base (ex : "items/abc123.jpg").
     */
    private function storePhoto($file): string
    {
        $filename = 'items/' . uniqid() . '.jpg';

        // Compression via Intervention Image : max 800px de large, qualité 80%
        $image = Image::read($file->getRealPath())
            ->scaleDown(width: 800)
            ->toJpeg(quality: 80);

        Storage::disk('public')->put($filename, (string) $image);

        return $filename;
    }

    /**
     * Vérifie que l'utilisateur connecté est bien le propriétaire du module.
     * Retourne une 403 sinon.
     */
    private function authorizeOwner(Module $module): void
    {
        if ($module->owner_id !== Auth::id()) {
            abort(403, 'Action non autorisée.');
        }
    }

    /**
     * Vérifie que l'item appartient bien au module passé en paramètre.
     * Protège contre les URL forgées (ex : /modules/1/items/99 où 99 appartient au module 2).
     */
    private function ensureBelongsToModule(Item $item, Module $module): void
    {
        if ($item->module_id !== $module->id) {
            abort(404);
        }
    }
}
