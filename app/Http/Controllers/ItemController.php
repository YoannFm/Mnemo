<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Item;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $rules = [
            'name_fr'         => $module->field_name_fr  ? 'required|string|max:255' : 'nullable|string|max:255',
            'name_alt'        => $module->field_name_alt ? 'required|string|max:255' : 'nullable|string|max:255',
            'function_text'   => $module->field_function ? 'required|string|max:2000' : 'nullable|string|max:2000',
            'photo'           => $module->field_photo    ? 'required|image|mimes:jpeg,png,jpg,webp|max:4096' : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_crop_data' => 'nullable|string',
        ];
        $validated = $request->validate($rules);

        $photoPath = $request->hasFile('photo') ? $this->storePhoto($request->file('photo')) : null;

        $item = $module->items()->create([
            'name_fr'       => $validated['name_fr'],
            'name_alt'      => $validated['name_alt'],
            'function_text' => $validated['function_text'] ?? '',
            'photo_path'    => $photoPath,
        ]);

        LogHelper::log('created_item', 'module', $module->id, ['item_id' => $item->id]);

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

        $rules = [
            'name_fr'         => $module->field_name_fr  ? 'required|string|max:255' : 'nullable|string|max:255',
            'name_alt'        => $module->field_name_alt ? 'required|string|max:255' : 'nullable|string|max:255',
            'function_text'   => $module->field_function ? 'required|string|max:2000' : 'nullable|string|max:2000',
            'photo'           => $module->field_photo    ? 'required|image|mimes:jpeg,png,jpg,webp|max:4096' : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_crop_data' => 'nullable|string',
        ];
        $validated = $request->validate($rules);

        $photoPath = $item->photo_path;

        if ($request->hasFile('photo')) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
            $photoPath = $this->storePhoto($request->file('photo'));
        }

        $item->update([
            'name_fr'       => $validated['name_fr'],
            'name_alt'      => $validated['name_alt'],
            'function_text' => $validated['function_text'],
            'photo_path'    => $photoPath,
        ]);

        LogHelper::log('updated_item', 'module', $module->id, ['item_id' => $item->id]);

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

        LogHelper::log('deleted_item', 'module', $module->id, ['item_id' => $item->id], 'warning');

        $item->delete();

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item supprimé.');
    }

    // ─────────────────────────────────────────────
    // Méthodes privées utilitaires
    // ─────────────────────────────────────────────

    /**
     * Affiche le formulaire d'import CSV.
     */
    public function showImportForm(Module $module)
    {
        $this->authorizeOwner($module);
        return view('items.import', compact('module'));
    }

    /**
     * Traite l'import CSV.
     * Format attendu : name_fr,name_en,function_text (sans en-tête ou avec)
     * La colonne photo est ignorée (import texte uniquement).
     */
    public function importCsv(Request $request, Module $module)
    {
        $this->authorizeOwner($module);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file    = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        $sep     = substr_count($content, ';') >= substr_count($content, ',') ? ';' : ',';
        $handle  = fopen($file->getRealPath(), 'r');
        $count   = 0;
        $errors  = [];
        $lineNum = 0;

        $reqNameFr  = \App\Models\Setting::get('item_required_name_fr', '1') === '1';
        $reqNameAlt = \App\Models\Setting::get('item_required_name_alt', '1') === '1';
        $reqFunc    = \App\Models\Setting::get('item_required_function', '0') === '1';

        while (($row = fgetcsv($handle, 1000, $sep)) !== false) {
            $lineNum++;

            if ($lineNum === 1 && in_array(strtolower(trim($row[0] ?? '')), ['name_fr', 'nom', 'nom_fr'])) {
                continue;
            }

            $nameFr   = trim($row[0] ?? '');
            $nameAlt  = trim($row[1] ?? '');
            $function = trim($row[2] ?? '');

            if (($reqNameFr && empty($nameFr)) || ($reqNameAlt && empty($nameAlt)) || ($reqFunc && empty($function))) {
                $errors[] = "Ligne {$lineNum} ignorée : champs obligatoires manquants.";
                continue;
            }

            $module->items()->create([
                'name_fr'       => mb_substr($nameFr, 0, 255),
                'name_alt'      => mb_substr($nameAlt, 0, 255),
                'function_text' => mb_substr($function, 0, 2000),
                'photo_path'    => null,
            ]);
            $count++;
        }

        fclose($handle);

        $message = "{$count} item(s) importé(s) avec succès.";
        if (!empty($errors)) {
            $message .= ' ' . count($errors) . ' ligne(s) ignorée(s).';
            session(['import_errors' => $errors]);
        }

        LogHelper::log('imported_items_csv', 'module', $module->id, ['count' => $count]);

        return redirect()->route('modules.show', $module)->with('success', $message);
    }

    // ─────────────────────────────────────────────
    // Méthodes privées utilitaires
    // ─────────────────────────────────────────────

    /**
     * Stocke une photo uploadée dans storage/public/items.
     * Compresse et redimensionne l'image avec la librairie GD de PHP.
     * Retourne le chemin relatif enregistré en base (ex : "items/uuid.jpg").
     */
    private function storePhoto($file): string
    {
        // Dimensions cibles : carré 800×800
        // - assez grand pour le zoom modal, assez petit pour le réseau
        // - crop centré pour que toutes les options Anki soient uniformes
        $targetSize = 800;
        $quality    = 85;

        $mime    = $file->getMimeType();
        $tmpPath = $file->getRealPath();

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmpPath),
            'image/png'  => @imagecreatefrompng($tmpPath),
            'image/webp' => @imagecreatefromwebp($tmpPath),
            default      => @imagecreatefromjpeg($tmpPath),
        };

        if (!$source) {
            throw new \RuntimeException('Impossible de lire l\'image uploadée.');
        }

        [$origW, $origH] = getimagesize($tmpPath);

        // 1. Redimensionner pour que le plus petit côté fasse exactement $targetSize
        //    (scale up uniquement si l'image est plus petite)
        $scale = $targetSize / min($origW, $origH);
        $scaledW = (int) round($origW * $scale);
        $scaledH = (int) round($origH * $scale);

        $scaled = imagecreatetruecolor($scaledW, $scaledH);

        if ($mime === 'image/png') {
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
        }

        imagecopyresampled($scaled, $source, 0, 0, 0, 0, $scaledW, $scaledH, $origW, $origH);
        imagedestroy($source);

        // 2. Crop centré pour obtenir $targetSize × $targetSize
        $cropX  = (int) round(($scaledW - $targetSize) / 2);
        $cropY  = (int) round(($scaledH - $targetSize) / 2);

        $canvas = imagecreatetruecolor($targetSize, $targetSize);
        imagecopy($canvas, $scaled, 0, 0, $cropX, $cropY, $targetSize, $targetSize);
        imagedestroy($scaled);

        // 3. Sauvegarder en JPEG
        $tmpOutput = tempnam(sys_get_temp_dir(), 'mnemo_') . '.jpg';
        imagejpeg($canvas, $tmpOutput, $quality);
        imagedestroy($canvas);

        // 4. Stocker dans storage/public/items
        $filename = 'items/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($filename, file_get_contents($tmpOutput));
        unlink($tmpOutput);

        return $filename;
    }

    /**
     * Vérifie que l'utilisateur connecté est bien le propriétaire du module.
     * Retourne une 403 sinon.
     */
    private function authorizeOwner(Module $module): void
    {
        if ($module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
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
